<?php


namespace AppBundle\Controller;


use AppBundle\Entity\Auction;
use AppBundle\Entity\Offer;
use AppBundle\Form\BidType;
use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class OfferController extends Controller
{

    /**
     * @Route("auction/buy/{id}", name="offer_buy", methods={"POST"})
     *
     * @param Auction $auction
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function buyAction(Auction $auction)
    {

        $this->denyAccessUnlessGranted("ROLE_USER");

        $offer = new Offer();
        $offer
            ->setAuction($auction)
            ->setPrice($auction->getPrice())
            ->setType(Offer::TYPE_BUY);

        $auction->setStatus(Auction::STATUS_FINISHED);
        $auction->setExpireAt(new DateTime());

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($auction);
        $entityManager->persist($auction);
        $entityManager->flush();

        $this->addFlash("success", "Congratulations! You bought {$auction->getTitle()} for {$auction->getPrice()} ");

        return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);

    }

    /**
     * @Route("/auction/bid/{id}", name="offer_bid", methods={"POST"})
     *
     * @param Request $request
     * @param Auction $auction
     * @return RedirectResponse
     */
    public function bidAction(Request $request, Auction $auction)
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        $offer = new Offer();
        $bidForm = $this->createForm(BidType::class, $offer);

        $bidForm->handleRequest($request);
        if ($bidForm->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $lastOffer = $entityManager
                ->getRepository(Offer::class)
                ->findOneBy(["auction" => $auction], ["price" => "DESC"]);

            if (isset($lastOffer)) {
                if ($offer->getPrice() <= $lastOffer->getPrice()) {
                    $this->addFlash("danger", "Your bid have to be greater than {$lastOffer->getPrice()}");

                    return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);
                }
            } else {
                if ($offer->getPrice() < $auction->getStartingPrice()) {
                    $this->addFlash("danger", "Your bid can't be smaller than starting price.");

                    return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);
                }
            }

            $offer
                ->setType(Offer::TYPE_BID)
                ->setAuction($auction);

            $entityManager->persist($offer);
            $entityManager->flush();

            $this->addFlash("success", "Success! Added bid {$offer->getPrice()} to auction : {$auction->getTitle()}.");
        } else {
            $this->addFlash("danger", "The failed adding bid");
        }

        return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);
    }
}