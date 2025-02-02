<?php
/**
 * NOTICE OF LICENSE
 * This source file is subject to the GNU Lesser General Public License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/lgpl-3.0.en.html
 *
 * @author     Blue Media S.A.
 * @copyright  Since 2015 Blue Media S.A.
 * @license    https://www.gnu.org/licenses/lgpl-3.0.en.html GNU Lesser General Public License
 */

namespace BlueMedia\OnlinePayments\Util;

use Exception;
use RuntimeException;
use SimpleXMLElement;

/**
 * XMLParser.
 */
class XMLParser
{
    /**
     * Parses XML response.
     *
     * @param string $xml
     *
     * @return SimpleXMLElement
     */
    public static function parse($xml)
    {
        try {
            return new SimpleXMLElement($xml);
        } catch (Exception $exception) {
            Logger::log(
                Logger::ERROR,
                $exception->getMessage(),
                ['exception' => $exception, 'xml' => $xml]
            );
            throw new RuntimeException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
