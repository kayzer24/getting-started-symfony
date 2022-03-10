<?php

namespace App\DataTransformer;

use App\Entity\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use function Symfony\Component\String\u;

class TagsTransformer implements DataTransformerInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param Collection<int, Tag> $value
     * @return string
     */
    public function transform(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return implode(',', $value
            ->map(static fn (Tag $tag): string => $tag->getName())
            ->toArray()
        );
    }

    /**
     * @param string $value
     * @return Collection<int, Tag>
     */
    public function reverseTransform(mixed $value): Collection
    {
        $tags = u($value)->split(',');

        $tagsCollection = new ArrayCollection();

        array_walk($tags, static fn(string &$tagName): string => u($tagName)->trim()->toString());

        $tagsRepository = $this->entityManager->getRepository(Tag::class);

        foreach ($tags as $tagName) {
            if ($tagName === '') {
                continue;
            }

            $tag = $tagsRepository->findOneBy(['name' => $tagName]);

            if ($tag === null) {
                $tag = new Tag();
                $tag->setName($tagName);

                $this->entityManager->persist($tag);
            }

            $tagsCollection->add($tag);
        }

        return $tagsCollection;
    }
}