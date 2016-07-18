<?php
/**
 *  用于保存各种错误的提示信息
 *
 * Class D1m_WeChat_ModelPromptMessage
 */
class D1m_WeChat_Model_PromptMessage extends Mage_Core_Model_Abstract
{

    /***
     *   message tips
     *
     * @var array
     */
    private $messages = array();

    private static $instance = null;

    /**
     * Returns a singleton instance of  Log Class
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new D1m_WeChat_Model_PromptMessage();
        }

        return self::$instance;
    }

    /**
     * Adds error to the list of message
     */
    public function setMessage($message)
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * Returns the last  message
     */
    public function getMessage()
    {
        return end($this->messages);
    }

    /**
     *  return all message
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Returns all the message concatenated with the $newline string
     *
     * @param string $newline
     * @return string
     */
    public function output($newline = "\n")
    {
        return implode($newline,$this->messages);
    }

    /**
     *  reset message
     *
     * @return $this
     */
    public function reset()
    {
        $this->messages = array();
        return $this;
    }
}