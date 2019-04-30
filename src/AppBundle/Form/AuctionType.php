<?php


namespace AppBundle\Form;


use AppBundle\Entity\Auction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AuctionType  extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder
        ->add("title",TextType::class,['label'=>"Title"])
        ->add("description", TextareaType::class,['label'=> 'Description'])
        ->add("price", NumberType::class,['label'=> 'Price'])
        ->add("startingPrice",NumberType::class,['label'=>'Starting price'])
        ->add("expireAt",DateTimeType::class,['label'=>'Expires at'])
        ->add("submit",SubmitType::class,['label'=> 'save']);
}

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(["data_class"=> Auction::class]);
}
}