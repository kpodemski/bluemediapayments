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

declare(strict_types=1);

namespace BluePayment;

class HookDispatcher
{
    const CLASSES = [
        Hook\Design::class,
        Hook\Admin::class,
        Hook\Payment::class,
    ];

    /**
     * List of available hooks
     * @var string[]
     */
    private $availableHooks = [];

    /**
     * Hook classes
     * @var Hook\AbstractHook[]
     */
    private $hooks = [];

    /**
     * Module
     *
     * @var \BluePayment
     */
    private $module;

    /**
     * Init hooks
     *
     * @param \BluePayment $module
     */
    public function __construct(\BluePayment $module)
    {
        $this->module = $module;

        foreach (self::CLASSES as $hookClass) {
            $hook = new $hookClass($this->module);
            $this->availableHooks = array_merge($this->availableHooks, $hook->getAvailableHooks());
            $this->hooks[] = $hook;
        }
    }

    /**
     * Get available hooks
     *
     * @return string[]
     */
    public function getAvailableHooks(): array
    {
        return $this->availableHooks;
    }

    /**
     * Find hook and dispatch it
     *
     * @param string $hookName
     * @param array $params
     *
     * @return mixed
     */
    public function dispatch($hookName, array $params = [])
    {
        $hookName = preg_replace('~^hook~', '', $hookName);

        foreach ($this->hooks as $hook) {
            if (method_exists($hook, $hookName)) {
                return call_user_func([$hook, $hookName], $params);
            }
        }

//        return null;
    }
}
