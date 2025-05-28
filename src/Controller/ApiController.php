<?php

namespace App\Controller;

use App\Service\PiDataService;
use App\Service\TwitterScrapingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    public function __construct(
        private PiDataService $piDataService,
        private TwitterScrapingService $twitterService
    ) {}

    #[Route('/pi/price', name: 'pi_price', methods: ['GET'])]
    public function getPiPrice(): JsonResponse
    {
        $stats = $this->piDataService->getMarketStats();
        
        return $this->json([
            'success' => true,
            'data' => [
                'price' => $stats['price'],
                'change_24h' => $stats['change_24h'],
                'volume_24h' => $stats['volume_24h'],
                'market_cap' => $stats['market_cap'],
                'last_updated' => $stats['last_updated']
            ]
        ]);
    }

    #[Route('/pi/history', name: 'pi_history', methods: ['GET'])]
    public function getPriceHistory(Request $request): JsonResponse
    {
        $period = $request->query->get('period', '24h');
        $history = $this->piDataService->getPriceHistory($period);
        
        return $this->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'history' => $history
            ]
        ]);
    }

    #[Route('/pi/stats', name: 'pi_stats', methods: ['GET'])]
    public function getFullStats(): JsonResponse
    {
        $marketStats = $this->piDataService->getMarketStats();
        $networkStats = $this->piDataService->getNetworkStats();
        $technicalIndicators = $this->piDataService->getTechnicalIndicators();
        $globalContext = $this->piDataService->getGlobalCryptoContext();
        
        return $this->json([
            'success' => true,
            'data' => [
                'market' => $marketStats,
                'network' => $networkStats,
                'technical' => $technicalIndicators,
                'global_context' => $globalContext
            ]
        ]);
    }

    #[Route('/twitter/tweets', name: 'twitter_tweets', methods: ['GET'])]
    public function getTwitterTweets(): JsonResponse
    {
        $tweets = $this->twitterService->scrapePiTeamTweets();
        
        return $this->json([
            'success' => true,
            'data' => [
                'tweets' => $tweets,
                'total_count' => count($tweets),
                'last_updated' => time()
            ]
        ]);
    }

    #[Route('/predictions', name: 'predictions', methods: ['GET'])]
    public function getPredictions(): JsonResponse
    {
        $tweets = $this->twitterService->scrapePiTeamTweets();
        $predictions = $this->twitterService->generatePredictions($tweets);
        $marketPredictions = $this->piDataService->getMarketPredictions();
        
        return $this->json([
            'success' => true,
            'data' => [
                'twitter_based' => $predictions,
                'market_based' => $marketPredictions,
                'generated_at' => time()
            ]
        ]);
    }

    #[Route('/news', name: 'news', methods: ['GET'])]
    public function getNews(): JsonResponse
    {
        $news = $this->piDataService->getPiNews();
        
        return $this->json([
            'success' => true,
            'data' => [
                'news' => $news,
                'total_count' => count($news)
            ]
        ]);
    }

    #[Route('/sentiment', name: 'sentiment', methods: ['GET'])]
    public function getSentimentAnalysis(): JsonResponse
    {
        $tweets = $this->twitterService->scrapePiTeamTweets();
        
        $sentiments = array_column($tweets, 'sentiment');
        $sentimentScores = array_column($tweets, 'sentiment_score');
        
        $positive = count(array_filter($sentiments, fn($s) => $s === 'positive'));
        $negative = count(array_filter($sentiments, fn($s) => $s === 'negative'));
        $neutral = count($sentiments) - $positive - $negative;
        
        $avgScore = count($sentimentScores) > 0 ? array_sum($sentimentScores) / count($sentimentScores) : 50;
        
        return $this->json([
            'success' => true,
            'data' => [
                'overall_sentiment' => $avgScore > 60 ? 'positive' : ($avgScore < 40 ? 'negative' : 'neutral'),
                'average_score' => round($avgScore, 2),
                'distribution' => [
                    'positive' => $positive,
                    'negative' => $negative,
                    'neutral' => $neutral
                ],
                'trends' => [
                    'bullish_indicators' => $this->getBullishIndicators($tweets),
                    'bearish_indicators' => $this->getBearishIndicators($tweets)
                ],
                'last_updated' => time()
            ]
        ]);
    }

    #[Route('/alerts', name: 'alerts', methods: ['GET'])]
    public function getAlerts(): JsonResponse
    {
        $stats = $this->piDataService->getMarketStats();
        $alerts = [];
        
        // Price movement alerts
        if (abs($stats['change_24h']) > 10) {
            $alerts[] = [
                'type' => 'price_movement',
                'severity' => 'high',
                'title' => 'Mouvement de prix significatif',
                'message' => sprintf('PI a %s de %.2f%% en 24h', 
                    $stats['change_24h'] > 0 ? 'augmenté' : 'diminué', 
                    abs($stats['change_24h'])
                ),
                'timestamp' => time()
            ];
        }
        
        // Volume alerts
        if ($stats['volume_24h'] > 100000000) {
            $alerts[] = [
                'type' => 'volume',
                'severity' => 'medium',
                'title' => 'Volume élevé détecté',
                'message' => 'Le volume des transactions a considérablement augmenté',
                'timestamp' => time()
            ];
        }
        
        // News-based alerts
        $tweets = $this->twitterService->scrapePiTeamTweets();
        $recentPositiveTweets = array_filter($tweets, function($tweet) {
            return $tweet['sentiment'] === 'positive' && 
                   strtotime($tweet['created_at']) > (time() - 3600); // Last hour
        });
        
        if (count($recentPositiveTweets) > 2) {
            $alerts[] = [
                'type' => 'sentiment',
                'severity' => 'medium',
                'title' => 'Sentiment très positif',
                'message' => 'Plusieurs annonces positives détectées dans la dernière heure',
                'timestamp' => time()
            ];
        }
        
        return $this->json([
            'success' => true,
            'data' => [
                'alerts' => $alerts,
                'total_count' => count($alerts)
            ]
        ]);
    }

    private function getBullishIndicators(array $tweets): array
    {
        $indicators = [];
        
        foreach ($tweets as $tweet) {
            if ($tweet['sentiment'] === 'positive') {
                foreach ($tweet['hashtags'] as $hashtag) {
                    if (in_array(strtolower($hashtag), ['partnership', 'mainnet', 'breakthrough', 'adoption'])) {
                        $indicators[] = ucfirst($hashtag) . ' mentionné';
                    }
                }
            }
        }
        
        return array_unique($indicators);
    }

    private function getBearishIndicators(array $tweets): array
    {
        $indicators = [];
        
        foreach ($tweets as $tweet) {
            if ($tweet['sentiment'] === 'negative') {
                foreach ($tweet['hashtags'] as $hashtag) {
                    if (in_array(strtolower($hashtag), ['delay', 'issue', 'concern', 'security'])) {
                        $indicators[] = ucfirst($hashtag) . ' mentionné';
                    }
                }
            }
        }
        
        return array_unique($indicators);
    }
}