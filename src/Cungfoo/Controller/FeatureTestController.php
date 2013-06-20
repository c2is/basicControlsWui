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
            $form = $app['form.factory']->createBuilder('form')
                ->add('url')
                ->getForm()
            ;

            if ('POST' == $request->getMethod()) {
                $form->bind($request);

                if ($form->isValid()) {
                    $trace = '';

                    $data = $form->getData();

                    $process = new Process('export BEHAT_PARAMS="context[parameters][base_url]='.$data['url'].'";cd ../tests/functionals/;../../bin/behat');

                    $process->run(function ($type, $buffer) use (&$trace) {

                        if ('err' === $type) {
                            $trace .= $buffer;
                        } else {
                            $trace .= $buffer;
                        }
                    });
                }
            }

            preg_match('/([0-9]) scénario(s)* \(([0-9]) (.*)\)/', $trace, $matches);

            $pass = true;
            if ($matches[4] === 'échecs') {
                $pass = false;
            }

            // display the form
            return $app->render('form.html.twig', array(
                'form'  => $form->createView(),
                'trace' => $trace,
                'pass'  => $pass,
            ));
        });
        return $ctl;
    }
}
