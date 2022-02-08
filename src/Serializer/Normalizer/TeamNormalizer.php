<?php

namespace App\Serializer\Normalizer;

use App\Entity\Team;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class TeamNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    const BASE_STORAGE_PATH = '/uploads/images/';

    /**
     * @var ObjectNormalizer
     */
    private $normalizer;
    
    /**
     * @var UrlHelper
     */
    private $urlHelper;

    public function __construct(
        ObjectNormalizer $normalizer,
        UrlHelper $urlHelper
    ) {
        $this->normalizer = $normalizer;
        $this->urlHelper = $urlHelper;
    }
    
    public function normalize(
        $team,
        string $format = null,
        array $context = []
    ): array {
        $data = $this->normalizer->normalize($team, $format, $context);

        if (!empty($team->getEmblem())) {
            $data['emblem'] = $this->urlHelper
                ->getAbsoluteUrl(self::BASE_STORAGE_PATH . $team->getEmblem());
        }
        
        return $data;
    }

    public function supportsNormalization(
        $data,
        string $format = null
    ): bool {
        return $data instanceof Team;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
