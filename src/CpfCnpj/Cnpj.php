<?php
 
namespace PragmaRX\Support\CpfCnpj;

/**
 * @see     https://raw.github.com/sinergia/brasil/master/Sinergia/Brasil/CPF.php
 * @package 
 */
class Cnpj extends CpfCnpj
{
    protected static $length = 14;

    protected static $format = '!\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}!';

    /**
     * Retorna o cnpj formatado como: 92.122.313/0001-30
     *
     * @param $cnpj
     * @return string
     *
     */
    public static function formatar($cnpj)
    {
        $cnpj = static::digitos($cnpj);

        if (strlen($cnpj) != 14) {
            return "";
        }

        $partes[]    = substr($cnpj, 0, 2);
        $partes[]    = substr($cnpj, 2, 3);
        $partes[]    = substr($cnpj, 5, 3);
        $filiais     = substr($cnpj, 8, 4);
        $verificador = substr($cnpj, 12);

        if (! empty($partes) && ! empty($filiais) && ! empty($verificador))
        {
            return implode(".", $partes) . '/' . $filiais . '-' . $verificador;
        }

        return null;
    }

    /**
     * Verifica se o dígito verificador está correto
     *
     * @param $cnpj
     *
     * @return bool
     */
    public static function validar($cnpj)
    {
        $cnpj = static::digitos($cnpj);
        if (strlen($cnpj) <> static::$length) {
            return false;
        }
        $regex = "/^0+$|^1+$|^2+$|^3+$|^4+$|^5+$|^6+$|^7+$|^8+$|^9+$/";
        if (preg_match($regex, $cnpj)) {
            return false;
        }
        // Primeiro dígito
        $multiplicadores = array (5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2);
        $soma            = 0;
        for ($i = 0; $i <= 11; $i++) {
            $soma += $multiplicadores[$i] * $cnpj[$i];
        }
        $d1 = 11 - ($soma % 11);
        if ($d1 >= 10) {
            $d1 = 0;
        }
        // Segundo dígito
        $multiplicadores = array (6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2);
        $soma            = 0;
        for ($i = 0; $i <= 12; $i++) {
            $soma += $multiplicadores[$i] * $cnpj[$i];
        }
        $d2 = 11 - ($soma % 11);
        if ($d2 >= 10) {
            $d2 = 0;
        }
        return $d1 == $cnpj[12] && $d2 == $cnpj[13];
    }
}
