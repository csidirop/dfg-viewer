<?php

namespace Slub\Dfgviewer\Controller;

use Kitodo\Dlf\Domain\Model\Document;
use Kitodo\Dlf\Common\MetsDocument;
use Kitodo\Dlf\Domain\Repository\StructureRepository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Controller class for the SRU plugin.
 *
 * Checks if the METS document contains a link to an SRU endpoint, and if so,
 * adds a search form to the pageview.
 *
 * @package    TYPO3
 * @subpackage    tx_dfgviewer
 * @access    public
 */
class TESTTESTController extends \Kitodo\Dlf\Controller\AbstractController
{
    public function mainAction()
    {
        echo '<script>alert("TESTTEST Controller")</script>'; //DEBUG
    }
}
