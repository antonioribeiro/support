<?php

namespace Support;

/**
 * @see     https://raw.github.com/sinergia/brasil/master/Sinergia/Brasil/CPF.php
 * @package 
 */
class CPF
{
    /**
     * Retorna apenas os dígitos do cpf
     *
     * @param $cpf
     *
     * @return string
     */
    public static function digitos($cpf)
    {
        return substr(preg_replace('![^\d]!', '', $cpf), 0, 11);
    }

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

        return implode(".", $partes) . '-' . $verificador;
    }

    /**
     * Retorna os últimos 2 dígitos verificadores
     *
     * @param $cpf
     *
     * @return string
     */
    public static function verificador($cpf)
    {
        $cpf = static::formatar($cpf);

        return substr($cpf, -2);
    }

    /**
     * Verifica se o cpf está no formato: 999.999.999-99
     *
     * @param $cpf
     *
     * @return bool
     */
    public static function validarFormato($cpf)
    {
        return preg_match('!\d{3}\.\d{3}\.\d{3}\-\d{2}!', $cpf) === 1;
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

    /**
     * Função responsável por gerar um cpf válido
     * @return string
     */
    public static function gerar()
    {
        $cpf = array ();
        for ($i = 0; $i < 9; $i++) {
            $cpf[$i] = rand(0, 9);
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
        $cpf[9] = $d1;

        // Segundo Dígito
        $soma = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma += ((11 - $i) * $cpf[$i]);
        }
        $d2 = 11 - ($soma % 11);
        if ($d2 >= 10) {
            $d2 = 0;
        }
        $cpf[10] = $d2;

        return static::formatar(implode("", $cpf));
    }
}