<?php
/*
 * StatusNet - the distributed open-source microblogging tool
 * Copyright (C) 2011, StatusNet, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('STATUSNET')) {
    exit(1);
}

class Social_capital extends Managed_DataObject
{
    public $__table = 'social_capital';

    public $id;
    public $profile_id;  // profile this is for
    public $index; // relative ordering of multiple values in the same field
    public $created;
    public $updated;

    static function schemaDef()
    {
        return array(
            // No need for i18n. Table properties.
            'description'
                => 'Social capital index',
            'fields'      => array(
                'id'          => array('type' => 'serial', 'not null' => true),
                'profile_id'  => array('type' => 'int', 'not null' => true),
                'social_index'       => array('type' => 'int'),
                'created'     => array(
                    'type'     => 'datetime',
                    'not null' => true
                 ),
                'updated'    => array(
                    'type' => 'timestamp',
                    'not null' => true
                ),
            ),
            'primary key' => array('id'),
            'unique keys' => array(
                'social_capital_profile_id'
                    => array('profile_id'),
            )
        );
    }
}
