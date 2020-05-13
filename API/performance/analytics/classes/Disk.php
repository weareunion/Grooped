<?php
/**
 * Copyright (c) 2019. This software is the property of Cor3 Design, LLC in cooperation with Union Development, LLC. Copying, distributing or using this software without proper permission is prohibited.
 */

namespace Moycroft\API\performance\analytics\Disk;


class Disk
{

    static function getCurrentLoad() {

        $disktotal = disk_total_space ('/');
        $diskfree  = disk_free_space  ('/');
        $diskuse   = ((($diskfree / $disktotal)));

        return $diskuse;

    }
}