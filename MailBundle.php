<?php

declare(strict_types=1);

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Bundle\MailBundle;

use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MailBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        SwiftmailerBundle::class;
    }
}
