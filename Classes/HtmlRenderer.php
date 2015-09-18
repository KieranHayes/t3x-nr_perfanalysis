<?php
declare(encoding = 'UTF-8');
/**
 * Part of nr_perfanalysis
 *
 * PHP version 5
 *
 * @category Netresearch
 * @package  Perfanalysis
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  http://www.gnu.org/licenses/agpl-3.0.html AGPL v3 or later
 * @link     http://www.netresearch.de/
 */
namespace Netresearch\NrPerfanalysis;

/**
 * Render the collected data
 *
 * @category Netresearch
 * @package  Perfanalysis
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  http://www.gnu.org/licenses/agpl-3.0.html AGPL v3 or later
 * @link     http://www.netresearch.de/
 */
class HtmlRenderer
{
    /**
     * Render statistics into HTML after everything is finished
     *
     * @param array  $arParams Parameter from render function
     * @param object $tsfe     TypoScript frontend controller
     *
     * @return void
     */

    public function contentPostProcOutput(
        $arParams,
        \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $tsfe
    ) {
        $tsfe->content = str_replace(
            '</body>',
            $this->genHtml() . '</body>',
            $tsfe->content
        );
    }

    /**
     * Generate HTML with statistics
     *
     * @return string HTML code
     */
    public function genHtml()
    {
        $counter = Counter::get();

        $htmlstr = array();
        if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            //only available since PHP 5.4.0
            $pagetime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
            $htmlstr[] = 'page: ' . number_format($pagetime, 3) . 's';
        }

        $counts = array();
        $times  = array();
        foreach ($counter->counts as $group => $events) {
            foreach ($events as $event => $number) {
                $counts[$group] += $number;
                $times[$group] += $counter->timesums[$group][$event];
            }
            $times[$group] = number_format($times[$group], 3);
        }
        ksort($counts);
        foreach ($counts as $group => $number) {
            $htmlstr[] = $group
                . ': ' . $counts[$group]
                . '⨯, ' . $times[$group] . 's';
        }

        $str = implode(', ', $htmlstr);
        $html = <<<HTM
<div id="perfanalysis" onclick="document.getElementById('perfanalysis').remove();" style="position: fixed; right: 0; bottom: 0; background-color: #333; color: #DDD; border-top: 1px solid #DDD; border-left: 1px solid #DDD; padding: 2px; font-size: 12px; z-index: 90000"><span id="perfanalysisbrowser"></span>
 $str
</div>
<script type="text/javascript">
if (typeof performance != "undefined") {
    window.addEventListener('load', function() {
        document.getElementById('perfanalysisbrowser').innerHTML = "browser: " + (new Date().getTime() - performance.timing.navigationStart) / 1000 + "s, ";
    });
}
</script>
HTM;
        return $html;
    }
}
?>
