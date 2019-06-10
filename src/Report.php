<?php


namespace Doyo\PhpSpec\CodeCoverage;


use Doyo\PhpSpec\CodeCoverage\Event\CoverageEvent;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Report implements EventSubscriberInterface
{
    /**
     * @var mixed
     */
    private $processor;

    /**
     * @var array
     */
    private $options;

    public function __construct($processor, array $options)
    {
        $this->processor = $processor;
        $this->options = $options;
    }

    public static function getSubscribedEvents()
    {
        return [
            CoverageEvent::REPORT => 'process'
        ];
    }

    public function process(CoverageEvent $event)
    {
        $codeCoverage = $event->getProcessor()->getCodeCoverage();
        $processor = $this->processor;
        $options = $this->options;
        $target = $options['target'];
        $io = $event->getConsoleIO();

        $dir = $target;

        if($options['type'] === 'file'){
            $dir = dirname($target);
        }

        if(!is_dir($dir)){
            mkdir($dir, 0775, true);
        }

        try{
            $processor->process($codeCoverage, $target);
            $io->writeln(sprintf(
                '<info>Generated code coverage to: <comment>%s</comment></info>',
                $target
            ));
        }catch (\Exception $exception){
            $io->writeln(sprintf(
                "<info>Failed generate code coverage to <comment>%s</comment>.\n%s</info>",
                $target,
                $exception->getMessage()
            ));
        }
    }

}