<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Messenger\CommandBusInterface;
use App\Messenger\QueryBusInterface;
use App\Repository\PostRepository;
use App\UseCase\Post\Create\CreateCommand;
use App\UseCase\Post\Listing\Listing;
use App\UseCase\Post\Listing\ListingQuery;
use App\UseCase\Post\Read\ReadQuery;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/posts', name: 'post_')]
final class PostController extends AbstractController
{
    #[Route('', name: 'list', methods: [Request::METHOD_GET])]
    public function list(QueryBusInterface $queryBus, Request $request): Response
    {
        /** @var Listing $listing */
        $listing = $queryBus->fetch(new ListingQuery($request->query->getInt('page', 1)));
        return $this->render('post/index.html.twig', $listing->getData());
    }

    #[Route('/create', name: 'create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    #[IsGranted('ROLE_USER')]
    public function create(
        Request $request,
        CommandBusInterface $commandBus
    ): Response {
        $form = $this->createForm(
            PostType::class,
            null,
            ['validation_groups' => ['Default','create']]
        )->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $commandBus->dispatch(new CreateCommand($form->getData()));

            return $this->redirectToRoute('post_read', ['id' => $post->getId()]);
        }

        return $this->renderForm('post/create.html.twig', ['form' => $form]);
    }

    #[Route('/{id}', name: 'read', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET])]
    public function read(QueryBusInterface $queryBus, int $id): Response
    {
        return $this->render('post/read.html.twig', [
            'post' => $queryBus->fetch(new ReadQuery($id))
        ]);
    }

    #[Route('/{id}/update', name: 'update', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET, Request::METHOD_POST])]
    #[IsGranted('POST_EDIT', subject: 'post')]
    public function update(
        Post $post,
        Request $request,
        EntityManagerInterface $entityManager,
        string $uploadDir
    ): Response {
        $form = $this->createForm(PostType::class, $post)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setPublishedAt(new \DateTimeImmutable());

            if ($post->getImageFile() !== null) {
                $post->setImage(
                    sprintf(
                        "%s.%s",
                        Uuid::v4(),
                        $post->getImageFile()->getClientOriginalExtension()
                    )
                );
                $post->getImageFile()->move($uploadDir, $post->getImage());
            }

            $entityManager->flush();

            return $this->redirectToRoute('post_read', ['id' => $post->getId()]);
        }

        return $this->render('post/update.html.twig', [
            'post' => $post,
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\d+'], methods: [Request::METHOD_POST])]
    #[IsGranted('POST_EDIT', subject: 'post')]
    public function delete(Post $post, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($post);
        $entityManager->flush();

        return $this->redirectToRoute('post_list');
    }
}
