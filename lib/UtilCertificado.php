<?php
/**
 * Clase para trabajar con certificados.
 *
 * @author 
 * @copyright 
 * @version 1.0.3
 */

class UtilCertificado {
    private $cerFileContent;
    private $keyFileContent; 
    private $keyPemFileContent;
    private $keyPassword;

    // Ruta de la libreria OpenSSL en el sistema operativo
    // Posible ruta en Windows: 'c:\OpenSSL-Win32\bin\openssl.exe'
    // Posible ruta en MAC/Linux: '/usr/bin/openssl'
    private static $openSslFile = 'C:\OpenSSL-Win32\bin\openssl.exe';
 

    public function loadFiles($cerFile, $keyFile, $keyPassword){
        if($cerFile && $keyFile && $keyPassword){
            if(file_exists($cerFile) && file_exists($keyFile)){
                $cerFileContent = file_get_contents($cerFile);
                if(!empty($cerFileContent)){
                    $this->cerFileContent = $cerFileContent;
                    $this->keyFileContent = file_get_contents($keyFile);
                    $this->keyPemFileContent = $this->getKeyPemContent(
                        $keyFile,
                        $keyPassword
                    );
                    if($this->keyPemFileContent){
                        $this->keyPassword = $keyPassword;
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function loadData($cerFileContent, $keyPemFileContent){
        $this->cerFileContent = $cerFileContent;
        $this->keyPemFileContent = $keyPemFileContent;
        return true;
    }

    public static function establecerRutaOpenSSL($ruta) {
        self::$openSslFile = $ruta;
    }

    public function firmarCadena($cadena, $algo){
        $resultado = null;
        $pKeyId = openssl_pkey_get_private($this->keyPemFileContent);
        $signOk = @openssl_sign(
            $cadena,
            $resultado,
            $pKeyId,
            $algo
        );
        openssl_free_key($pKeyId);

        if($signOk){
            return base64_encode($resultado);
        }

        return null;
    }

    public function getRFC(){
        $d = openssl_x509_parse(
            $this->getCerPemContent(),
            true
        );

        if($d) {
            $rfcs = explode(
                '/',
                str_replace(' ', '', $d['subject']['x500UniqueIdentifier'])
            );
            return strtoupper($rfcs[0]);
        }

        return null;
    }

    public function getRangoValidez(){
        $d = openssl_x509_parse(
            $this->getCerPemContent(),
            true
        );

        if($d) {
            return array(
                'from' => $d['validFrom_time_t'],
                'to'   => $d['validTo_time_t']
            );
        }

        return null;
    }

    public function getNombre(){
        $d = openssl_x509_parse(
            $this->getCerPemContent(),
            true
        );        

        if($d && !empty($d['subject']['CN'])) {
            return $d['subject']['CN'];
        }

        return null;
    }

    public function getNumeroCertificado(){
        $d = openssl_x509_parse(
            $this->getCerPemContent(),
            true
        );

        /**codigo agregado tomado de la version 3.3*/
        if ($d && !empty($d['serialNumberHex'])){
            $hex = $d['serialNumberHex'];
            $num ='';
            for ($i=0;$i<strlen($hex);$i+=2){
                $num .= chr(hexdec(substr($hex, $i, 2)));
            }
            return $num;
        }

        /**codigo original comentado */
        /**if($d) {
            $number = $d['serialNumber'];

            $hexvalues = array('0','1','2','3','4','5','6','7', '8','9','A','B','C','D','E','F');
            $hexval = '';
            while($number != '0'){
                $hexval = $hexvalues[bcmod($number,'16')].$hexval;
                $number = bcdiv($number,'16',0);
            }
            $number = '';
            $len = strlen($hexval);
            for($i=0; $i<$len;$i+=2){
                $number .=  substr($hexval, $i+1, 1);
            }

            return $number;
        }*/

        /**codigo agregado tomado de la version 3.3*/
        if($d && !empty($d['serialNumber'])) {
            $number = $d['serialNumber'];
            $hexvalues = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
            $hexval = '';
            while($number != '0'){
                $hexval = $hexvalues[bcmod($number,'16')].$hexval;
                $number = bcdiv($number,'16',0);
            }
            $number = '';
            $len = strlen($hexval);
            for($i=0; $i<$len;$i+=2){
                $number .=  substr($hexval, $i+1, 1);
            }

            return $number;
        }

        return null;
    }

    public function getTipoCertificado(){
        $d = openssl_x509_parse(
            $this->getCerPemContent(),
            true
        );

        if($d) {
            $keyUsage = explode(',', str_replace(' ', '', $d['extensions']['keyUsage']));
            $count = count($keyUsage);
            if($count > 0 && in_array('DigitalSignature',$keyUsage) && in_array('NonRepudiation',$keyUsage)){
                if($count == 2){
                    return 'CSD';
                }elseif($count == 4){
                    return 'FIEL';
                }
            }
        }

        return null;
    }

    private function getCerPemContent(){
        return self::der2pem($this->cerFileContent);
    }

    private function getKeyPemContent($keyFile, $keyPwd){
        if (!file_exists(self::$openSslFile)) {
            throw new Exception('La ruta a la librería OpenSSL no es correcta.');
        }

        $cmd = self::$openSslFile.' pkcs8 -inform DER -in '.$keyFile.' -passin pass:'.$keyPwd;
        $res = shell_exec($cmd);
        if(!empty($res)){
            return $res;
        }

        return null;
    }

     /**codigo agregado tomado de la version 3.3*/
    public function getKeyPemFileContent() {
        return $this->keyPemFileContent;
    }

    public function getKeyPassword() {
        return $this->keyPassword;
    }

    public function getCerFileContent() {
        return $this->cerFileContent;
    }

    public function getKeyFileContent() {
        return $this->keyFileContent;
    }


    private static function der2pem($der_data) {
        return '-----BEGIN CERTIFICATE-----'.PHP_EOL
            .chunk_split(base64_encode($der_data), 64, PHP_EOL)
            .'-----END CERTIFICATE-----'.PHP_EOL;
    }

    public function toBase64(){
        return str_replace(
            array('\n', '\r'),
            '',
            base64_encode($this->cerFileContent)
        );
    }
}