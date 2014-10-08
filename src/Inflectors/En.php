<?php

namespace PragmaRX\Support\Inflectors;

use Illuminate\Support\Pluralizer;

class En implements InflectorInterface {

    public function plural($word)
    {
        return Pluralizer::plural($word);
    }
    
    public function singular($word)
    {
	    return Pluralizer::singular($word);
    }

}
