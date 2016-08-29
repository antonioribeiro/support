<?php
 
namespace PragmaRX\Support\CpfCnpj;

/**
 * @see     https://raw.github.com/sinergia/brasil/master/Sinergia/Brasil/CPF.php
 * @package 
 */
class Cpf extends CpfCnpj
{
    protected static $length = 11;

    protected static $format = '!\d{3}\.\d{3}\.\d{3}\-\d{2}!';

    /**
     * Retorna o cpf formatado como: 999.999.999-99
     *
     * @param $cpf
     *
     * @return string
     */
    public static function formatar($cpf)
    {
        $cpf = static::digitos($cpf);

        if (strlen($cpf) != 11) {
            return "";
        }

        $partes      = str_split($cpf, 3);
        $verificador = array_pop($partes);

        if (! empty($partes = implode(".", $partes)) && ! empty($verificador))
        {
            return  $partes . '-' . $verificador;
        }

        return null;
    }

    /**
     * Verifica se o dígito verificador está correto
     *
     * @param $cpf
     *
     * @return bool
     */
    public static function validar($cpf)
    {
        $cpf = static::digitos($cpf);

        if (strlen($cpf) <> 11) {
            return false;
        }

        $regex = "/^0+$|^1+$|^2+$|^3+$|^4+$|^5+$|^6+$|^7+$|^8+$|^9+$/";
        if (preg_match($regex, $cpf)) {
            return false;
        }

        // Primeiro dígito
        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma += ((10 - $i) * $cpf[$i]);
        }
        $d1 = 11 - ($soma % 11);
        if ($d1 >= 10) {
            $d1 = 0;
        }

        // Segundo Dígito
        $soma = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma += ((11 - $i) * $cpf[$i]);
        }
        $d2 = 11 - ($soma % 11);
        if ($d2 >= 10) {
            $d2 = 0;
        }
        return $d1 == $cpf[9] && $d2 == $cpf[10];
    }
}
