<?php

namespace PragmaRX\Support\Inflectors;

/**
 *    --------------------------------------------------------------------------------------
 *        TO DO
 *    --------------------------------------------------------------------------------------
 *    Precisa analisar também a questão das  palavras  compostas,  onde,  no  português,  na
 *    maioria das vezes, a pluralização ocorre no primeiro termo.
 */
 
class PtBr implements InflectorInterface {
    /**
      *    Regras de singularização/pluralização
      */
    protected $rules = array(
      //singular     plural
        'ão'    => 'ões', 
        'ês'    => 'eses', 
        'm'     => 'ns', 
        'l'     => 'is', 
        'r'     => 'res', 
        'x'     => 'xes', 
        'z'     => 'zes', 
    );

    /**
      *    Exceções às regras
      */
    protected $exceptions = array(
        'cidadão' => 'cidadãos',
        'mão'     => 'mãos',
        'qualquer'=> 'quaisquer',
        'campus'  => 'campi',
        'lápis'   => 'lápis',
        'ônibus'  => 'ônibus',
    );

    /**
     *    Passa uma palavra para o plural
     *    
     *    @param  string $word A palavra que deseja ser passada para o plural
     *    @return string
     */
    public function plural($word)
    {
        //Pertence a alguma exceção?
        if (array_key_exists($word, $this->exceptions)):
            return $this->exceptions[$word];
        //Não pertence a nenhuma exceção. Mas tem alguma regra?
        else:
            foreach($this->rules as $singular=>$plural):
                if (preg_match("({$singular}$)", $word))
                    return preg_replace("({$singular}$)", $plural, $word);
            endforeach;
        endif;
        //Não pertence às exceções, nem às regras.
        //Se não terminar com "s", adiciono um.
        if (substr($word, -1) !== 's')
            return $word . 's';
        return $word;
    }
    
    /**
     *    Passa uma palavra para o singular
     *    
     *    @param  string $word A palavra que deseja ser passada para o singular
     *    @return string
     */
    public function singular($word)
    {
        //Pertence às exceçÃµes?
        if (in_array($word, $this->exceptions)):
            $invert = array_flip($this->exceptions);
            return $invert[$word];
        //Não é execeção.. Mas pertence a alguma regra?
        else:
            foreach($this->rules as $singular => $plural):
                if (preg_match("({$plural}$)",$word))
                    return preg_replace("({$plural}$)", $singular, $word);
            endforeach;
        endif;
        //Nem é exceção, nem tem regra definida. 
        //Apaga a última somente se for um "s" no final
		if (substr($word, -1) == 's')
            return substr($word, 0, -1);
        return $word;
    }
}
