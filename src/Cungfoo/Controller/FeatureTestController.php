<?php

namespace Cungfoo\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Process\Process;

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
                'url' => 'http://www.google.fr',
            );

            $form = $app['form.factory']->createBuilder('form', $data)
                ->add('url')
                ->getForm();

            if ('POST' == $request->getMethod()) {
                $form->bind($request);

                if ($form->isValid()) {
                    $data = $form->getData();
                    echo "<b>Checking ".$data['url']."</b>";
                    $i = 0; $minBuff = "";
                    while($i < (1500 - strlen(ob_get_contents()))){ $minBuff .= " "; $i++;}
                    echo $minBuff;
                    $process = new Process('export BEHAT_PARAMS="context[parameters][base_url]='.$data['url'].'";cd ../tests/functionals/;../../bin/behat');
                    if(ini_get("output_buffering") == "Off") ob_start();

                    echo "<pre>";
                    $process->run(function ($type, $buffer) {
                        if ('err' === $type) {
                            echo ''.$buffer;
                        } else {
                            echo ''.$buffer;

                            flush();

                            ob_start();
                        }
                    });
                    echo "</pre>";
                }
            }

            // display the form
            return $app->render('form.html.twig', array('form' => $form->createView()));
        });
        return $ctl;
    }
}