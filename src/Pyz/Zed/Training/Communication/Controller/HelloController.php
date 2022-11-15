<?php

namespace Pyz\Zed\Training\Communication\Controller;

use Generated\Shared\Transfer\AntelopeTransfer;
use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class HelloController extends AbstractController
{
    /**
     * @return array
     * @method \Pyz\Zed\Training\TrainingConfig getConfig()
     */
    public function indexAction(Request $req)
    {
        $mystore = $this->getFactory()->getConfig()->getMyDefaultStore();
        $antelopeTransfer = new AntelopeTransfer();
        $antelopeTransfer->setName('Oskar');
        return $this->viewResponse([
            'store' => $mystore,
            'antelope' => $antelopeTransfer
        ]);
    }
}
