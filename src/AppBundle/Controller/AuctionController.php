<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Auction;
use AppBundle\Form\AuctionType;
use AppBundle\Form\BidType;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuctionController extends Controller
{

    /**
     * @Route("/", name = "auction_index")
     *
     * @return Response
     */
    public function indexAction()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $auctions = $entityManager->getRepository(Auction::class)->findBy(["status" => Auction::STATUS_ACTIVE]);

        return $this->render("Auction/index.html.twig", ["auctions" => $auctions]);
    }


    /**
     * @Route("auction/details/{id}", name = "auction_details")
     *
     * @param Auction $auction
     *
     * @return Response
     */
    public function detailsAction(Auction $auction)
    {
        if ($auction->getStatus() === Auction::STATUS_FINISHED) {
            return $this->render("Auction/finished.html.twig", ["auction" => $auction]);
        }

        $deleteForm = $this->createFormBuilder()
            ->setAction($this->generateUrl("auction_delete", ["id" => $auction->getId()]))
            ->setMethod(Request::METHOD_DELETE)
            ->add("submit", SubmitType::class, ["label" => "Delete"])
            ->getForm();

        $finishForm = $this->createFormBuilder()
            ->setAction($this->generateUrl("auction_finish", ["id" => $auction->getId()]))
            ->add("submit", SubmitType::class, ["label" => "Finish"])
            ->getForm();
        $buyForm = $this->createFormBuilder()
            ->setAction($this->generateUrl("offer_buy", ["id" => $auction->getId()]))
            ->add("submit", SubmitType::class, ["label" => "Buy now"])
            ->getForm();

        $bidForm = $this->createForm(
            BidType::class,
            null,
            ["action" => $this->generateUrl("offer_bid", ["id" => $auction->getId()])
            ]
        );

        return $this->render(
            "Auction/details.html.twig",
            ["auction" => $auction,
                "deleteForm" => $deleteForm->createView(),
                "finishForm" => $finishForm->createView(),
                "buyForm" => $buyForm->createView(),
                "bidForm" => $bidForm->createView()
            ]
        );
    }


    /**
     * @Route("/auction/add", name="auction_add")
     *
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function addAction(Request $request)
    {
        $auction = new Auction();

        $form = $this->createForm(AuctionType::class, $auction);


        if ($request->isMethod("post")) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $auction
                    ->setStatus(Auction::STATUS_ACTIVE);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist(($auction));
                $entityManager->flush();

                $this->addFlash("success", "Success! Added auction : {$auction->getTitle()}.");

                return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);
            }
            $this->addFlash("error", "Error! Not added auction : {$auction->getTitle()}.");
        }

        return $this->render("Auction/add.html.twig", ["form" => $form->createView()]);
    }

    /**
     * @Route("/auction/edit/{id}", name="auction_edit")
     *
     * @param Request $request
     * @param Auction $auction
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function editAction(Request $request, Auction $auction)
    {
        $form = $this->createForm(AuctionType::class, $auction);

        if ($request->isMethod("post")) {
            $form->handleRequest($request);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($auction);
            $entityManager->flush();

            $this->addFlash("success", "Success! Edited auction : {$auction->getTitle()}.");

            return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);
        }

        return $this->render("Auction/edit.html.twig", ["form" => $form->createView()]);
    }

    /**
     * @Route("/auction/delete/{id}", name="auction_delete", methods={"DELETE"})
     *
     * @param Auction $auction
     *
     * @return RedirectResponse
     */
    public function deleteAction(Auction $auction)
    {
        $entityMenager = $this->getDoctrine()->getManager();
        $entityMenager->remove($auction);
        $entityMenager->flush();

        $this->addFlash("success", "Success! Deleted auction : {$auction->getTitle()}.");

        return $this->redirectToRoute("auction_index");
    }

    /**
     * @Route("/auction/finish/{id}", name="auction_finish", methods={"POST"})
     *
     * @param Auction $auction
     *
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function finishAction(Auction $auction)
    {
        $auction
            ->setExpireAt(new DateTime())
            ->setStatus(Auction::STATUS_FINISHED);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($auction);
        $entityManager->flush();

        $this->addFlash("success", "Finished auction : {$auction->getTitle()}.");

        return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);
    }
}
