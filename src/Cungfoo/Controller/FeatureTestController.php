<?php

namespace Cungfoo\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class FeatureTestController implements ControllerProviderInterface
{
    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app) {
        $ctl = $app['controllers_factory'];


        $ctl->match('/', function (Request $request) use ($app) {
            // some default data for when the form is displayed the first time
            $data = array(
                'name' => 'Url to check',
            );

            $form = $app['form.factory']->createBuilder('form', $data)
                ->add('name')
                ->getForm();

            if ('POST' == $request->getMethod()) {
                $form->bind($request);

                if ($form->isValid()) {
                    $data = $form->getData();

                    // do something with the data

                    // redirect somewhere
                    //return $app->redirect('http://www.google.fr/');
                }
            }

            // display the form
            return $app->render('form.html.twig', array('form' => $form->createView()));
        });
        return $ctl;
    }
}