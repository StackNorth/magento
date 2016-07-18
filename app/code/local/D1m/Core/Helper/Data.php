<?php
class D1m_Core_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getTranslateJson($data)
    {
        $data = (array)$data;
        $result = array();
        foreach ($data as $module => $string) {
            $helper = '';
            try {
                class_exists(Mage::getConfig()->getHelperClassName($module), FALSE) and
                $helper = Mage::helper($module);
            }
            catch (Exception $e) {
            }
            $helper instanceof Mage_Core_Helper_Abstract or $helper = $this;
            $result[$string] = $helper->__($string);
        }
        return json_encode($result);
    }

    /**
     * Read last line of a text file
     *
     * @param string $filename
     * @return string
     */
    public function readLastLineOfFile($filename)
    {
        $line = '';

        if (is_file($filename) && $f = @fopen($filename, 'r')) {
            $cursor = -1;

            fseek($f, $cursor, SEEK_END);
            $char = fgetc($f);

            /**
             * Trim trailing newline chars of the file
             */
            while ($char === "\n" || $char === "\r") {
                fseek($f, $cursor--, SEEK_END);
                $char = fgetc($f);
            }

            /**
             * Read until the start of file or first newline char
             */
            while ($char !== FALSE && $char !== "\n" && $char !== "\r") {
                /**
                 * Prepend the new char
                 */
                $line = $char . $line;
                fseek($f, $cursor--, SEEK_END);
                $char = fgetc($f);
            }
            fclose($f);
        }
        return $line;
    }

    /**
     * Get email name which before '@', e.g.: abc@qq.com will return 'abc'
     */
    public function getEmailName($email)
    {
        $name = '';
        if (strlen($email) && false!==strpos($email,'@')) {
            $name = substr($email, 0, strpos($email, '@'));
        }
        return $name;
    }



    /**
     * Genrate index stock file, for fetchint updatedTime for stockcompare module.
     */
    public function generateIndexStockTmpFile()
    {
        $ret = false;
        clearstatcache();

        $baseDir = Mage::getBaseDir();
        $allStockLog = $baseDir . '/var/log/updateAllStock.log';
        $curStockLog = $baseDir . '/var/log/updatestock.log';

        if (is_file($allStockLog) && is_file($curStockLog)) {
            $rebuildCache = true;
            $dateId = join('__', array(
                date('YmdHis',filemtime($allStockLog)), 
                date('YmdHis',filemtime($curStockLog)), 
            ));

            $allStockLogTmp = $baseDir . '/var/log/tmp.updatestock.log';
            if (is_file($allStockLogTmp)) {
                // have tmp file, check if it's old.
                $fp = fopen($allStockLogTmp,'r');
                if ($fp) {
                    $firstLine = trim(fgets($fp));
                    if ($firstLine == $dateId){
                        $rebuildCache = false;
                    }
                    fclose($fp);
                }
            }

            if ($rebuildCache) {
                $date = date('Y-m-d');
                $prevDate = date('Y-m-d', strtotime("-1 day", strtotime($date)));
                file_put_contents($allStockLogTmp, "$dateId\n");
                $cmds = array(
                    "grep \"$prevDate\" $allStockLog >> $allStockLogTmp" ,
                    "grep \"$date\" $allStockLog >> $allStockLogTmp" ,
                    "grep \"$date\" $curStockLog >> $allStockLogTmp" ,
                );
                foreach($cmds as $cmd) {
                    system($cmd, $return);
                }
            }
        }else{
            Mage::log("[-] Log file $allStockLog and $curStockLog not found!" . __METHOD__ );
        }
        return true;
    }

    public function getIndexStockTime($sku)
    {
        $ret = '';
        if (strlen($sku)) {
            // generate file
            $this->generateIndexStockTmpFile();
            $baseDir = Mage::getBaseDir();
            $allStockLogTmp = $baseDir . '/var/log/tmp.updatestock.log';
            $cmd = "grep \"$sku\" $allStockLogTmp ";
            exec($cmd, $output, $return);
            // if grep found return==0; last line can be fetched via end($output) 
            if ($return==0 && is_array($output) && ($output = trim(end($output)))) {
                $output = explode(' ', $output);
                $ret = reset($output);
            }
        }
        return $ret;
    }

}
