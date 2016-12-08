<?php
/**
 * v6.0.0-svn
 *
 * @author      Steeve Andrian Salim
 * @created     17/11/2016 15:58
 * @copyright   Copyright (c) 2016 Steeve Andrian Salim
 */

namespace O2System\Kernel\Services;


use O2System\Spl\Containers\SplClosureContainer;

class Shutdown extends SplClosureContainer
{
    public function execute ()
    {
        foreach ( $this as $offset => $closure ) {
            call_user_func( $closure );
        }
    }
}