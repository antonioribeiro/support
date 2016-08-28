<?php
 
namespace PragmaRX\Support\CpfCnpj;

use Illuminate\Support\Str;

/**
 * @see     https://raw.github.com/sinergia/brasil/master/Sinergia/Brasil/CPF.php
 * @package 
 */
abstract class CpfCnpj
{
    /**
     * Retorna apenas os dígitos do cpf
     *
     * @param $cpfCnpj
     *
     * @return string
     */
    public static function digitos($cpfCnpj)
    {
        return substr(preg_replace('![^\d]!', '', $cpfCnpj), 0, static::$length);
    }

    /**
     * Retorna os últimos 2 dígitos verificadores
     *
     * @param $cpfCnpj
     *
     * @return string
     */
    public static function verificador($cpfCnpj)
    {
        $cpfCnpj = static::formatar($cpfCnpj);

        return substr($cpfCnpj, -2);
    }

    /**
     * Verifica se o cpf está no formato: 999.999.999-99
     *
     * @param $cpfCnpj
     *
     * @return bool
     */
    public static function validarFormato($cpfCnpj)
    {
        return preg_match(static::$format, $cpfCnpj) === 1;
    }

    /**
     * Verifica se o dígito verificador está correto
     *
     * @param $cpfCnpj
     *
     * @return bool
     */
    public static function validar($cpfCnpj)
    {
        $cpfCnpj = static::digitos($cpfCnpj);

        if (strlen($cpfCnpj) <> static::$length) {
            return false;
        }

        $regex = "/^0+$|^1+$|^2+$|^3+$|^4+$|^5+$|^6+$|^7+$|^8+$|^9+$/";
        if (preg_match($regex, $cpfCnpj)) {
            return false;
        }

        // Primeiro dígito
        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma += ((10 - $i) * $cpfCnpj[$i]);
        }
        $d1 = static::$length - ($soma % static::$length);
        if ($d1 >= 10) {
            $d1 = 0;
        }

        // Segundo Dígito
        $soma = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma += ((static::$length - $i) * $cpfCnpj[$i]);
        }
        $d2 = static::$length - ($soma % static::$length);
        if ($d2 >= 10) {
            $d2 = 0;
        }
        return $d1 == $cpfCnpj[9] && $d2 == $cpfCnpj[10];
    }

    /**
     * Função responsável por gerar um cpf válido
     * @return string
     */
    public static function gerar()
    {
        $number = static::random_str(static::$length-2);
        $final = '';
        $counter = 0;

        while (! static::validar($final))
        {
            $final = $number . sprintf('%02d', $counter);

            $counter++;
        }

        return $final;
    }

    private static function random_str($length, $keyspace = '0123456789') {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;

        if ($max < 1) {
            throw new Exception('$keyspace must be at least two characters long');
        }

        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }

        return $str;
    }
}
