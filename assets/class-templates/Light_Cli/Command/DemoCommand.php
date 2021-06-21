<?php


namespace Ling\Light_PlanetInstallerXXX\CliTools\Command;


use Ling\CliTools\Input\InputInterface;
use Ling\CliTools\Output\OutputInterface;
use Ling\Light_Cli\Helper\LightCliFormatHelper;


/**
 * The DemoCommand class.
 *
 */
class DemoCommand extends LightPlanetInstallerBaseCommand
{

    /**
     * Builds the DemoCommand instance.
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * @implementation
     */
    protected function doRun(InputInterface $input, OutputInterface $output)
    {

        $currentDirectory = getcwd();


        $optionA = $input->hasFlag("option-a");
        $output->write("This demo command does nothing special, but gives you a starting point." . PHP_EOL);


    }



    //--------------------------------------------
    // LightCliCommandInterface
    //--------------------------------------------
    /**
     * @overrides
     */
    public function getDescription(): string
    {
        $co = LightCliFormatHelper::getConceptFmt();
        $url = LightCliFormatHelper::getUrlFmt();
        return "
 This command does nothing special, but gives you a starting point to start creating your own commands.
 ";
    }

    /**
     * @overrides
     */
    public function getParameters(): array
    {
        $co = LightCliFormatHelper::getConceptFmt();
        $url = LightCliFormatHelper::getUrlFmt();

        return [
//            "dstFile" => [
//                " the path where to write the map. If null (by default), we put it in a <b>_universe_maps</b> directory at the root of your app.",
//                false,
//            ],
        ];
    }

    /**
     * @overrides
     */
    public function getAliases(): array
    {
        $co = LightCliFormatHelper::getConceptFmt();
        $url = LightCliFormatHelper::getUrlFmt();

//        return [
//            "map " => "lpi create_map",
//        ];
        return [];
    }


}