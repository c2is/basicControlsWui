<?php

namespace Cungfoo\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Process\Process;
use Symfony\Component\Validator\Constraints as Assert;

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
                ->add('url', 'text', array(
                    'constraints' => array(new Assert\Url())))
                ->getForm()
            ;
            $trace = "";
            $pass = true;
            $data = "";
            $formErrors = "";
            if ('POST' == $request->getMethod()) {
                $form->bind($request);

                if ($form->isValid()) {

                    $data = $form->getData();

                }
                else {
                    $formErrors = "Bad value given";
                }

            }

            $urlToCheck = (is_array($data))? $data['url']:"";
            // display the form
            return $app->render('form.html.twig', array(
                'form'  => $form->createView(),
                'trace' => $trace,
                'pass'  => $pass,
                'urlToCheck'  => $urlToCheck,
                'formErrors' => $formErrors,
            ));
        });

        $ctl->match('/iframe', function (Request $request) use ($app) {


            $urlToCheck = $request->query->get('urlToCheck');

            $stream = function () use($urlToCheck, $request){
                echo "<!DOCTYPE html>";
                echo "<html>";
                echo "<head>";
                echo '<link href="'.$request->getBasePath().'/assets/css/custom.css" rel="stylesheet">';
                echo '<script src="'.$request->getBasePath().'/assets/theme-backend/js/scripts.js"></script>';
                echo '<script src="'.$request->getBasePath().'/assets/js/custom.js"></script>';
                echo '<script language="JavaScript">
                $(window.parent.document).ready(function() {
                    $("body").animate({ scrollTop: $(document).height() }, 7000);
                });
                </script>';
                echo "<head>";
                echo "<body>";
                echo "<pre id='stdout'  class=''>";
                flush();
                $procTimeOut = 3600;
                $process = new Process('export BEHAT_PARAMS="context[parameters][base_url]='.$urlToCheck.'";cd ../tests/functionals/;../../bin/behat',null, null, null,$procTimeOut);
                $process->run(function ($type, $buffer) {
                    if ('err' === $type) {
                        echo $buffer;
                    } else {
                        echo $buffer;
                        flush();
                    }
                });
                echo "</pre>";
                if ($process->getExitCode() == 0) {
                    echo '<script language="JavaScript">$("#stdout").attr("class", "pass");</script>';
                    flush();
                }
                else {
                    echo '<script language="JavaScript">$("#stdout").attr("class", "failed");</script>';
                    flush();
                }
                echo "</body></html>";
                flush();
            };
            return $app->stream($stream);
        });
        return $ctl;
    }
}
