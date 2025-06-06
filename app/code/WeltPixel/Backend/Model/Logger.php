<?php
namespace WeltPixel\Backend\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Logger extends \Magento\Framework\Logger\Monolog
{
    const XML_PATH_WELTPIXEL_DEVELOPER_LOGGING = 'weltpixel_backend_developer/logging/disable_broken_reference';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Logger constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param string $name
     * @param array $handlers
     * @param array $processors
     */
    public function __construct(ScopeConfigInterface $scopeConfig, $name, array $handlers = [], array $processors = [])
    {
        $this->scopeConfig = $scopeConfig;
        $handlers = array_values($handlers);

        parent::__construct($name, $handlers, $processors);
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * @param string $message The log message
     * @param array $context The log context
     * @return void
     */
    public function warning($message, array $context = []): void
    {
        $result = $this->_parseLogMessage($message, $context);
        if ($result !== false) {
            parent::warning($message, $context);
        }
    }

    /**
     * Adds a log record at the INFO level.
     *
     * @param string $message The log message
     * @param array $context The log context
     * @return void
     */
    public function info($message, array $context = []): void
    {
        $result = $this->_parseLogMessage($message, $context);
        if ($result !== false) {
            parent::info($message, $context);
        }
    }

    /**
     * Parses and determines if the log message should be logged based on configuration.
     *
     * @param string $message
     * @param array $context
     * @return bool
     */
    protected function _parseLogMessage($message, array $context): bool
    {
        $isLogEnabled = $this->scopeConfig->getValue(
            self::XML_PATH_WELTPIXEL_DEVELOPER_LOGGING,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $pos = strpos($message, 'Broken reference');
        if (!$isLogEnabled && $pos !== false) {
            return false;
        }

        return true;
    }
}
