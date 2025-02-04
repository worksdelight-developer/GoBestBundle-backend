<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * @return array
     */
    public function hosts()
    {
        return [
            $this->allSubdomainsOfApplicationUrl(),
        ];
    }
}


Error: Null check operator used on a null value
Exception Occurred!
API Response Time: 783 ms
StackTrace: #0      State.context (package:flutter/src/widgets/framework.dart:954)
#1      _SplashSecondScreenState._navigateToNextScreen (package:diet_angel/pages/splash_second_phase.dart:507)
#2      _SplashSecondScreenState.navigationPage (package:diet_angel/pages/splash_second_phase.dart:480)
<asynchronous suspension>