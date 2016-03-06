<?php
/*
 * Social Capital Plugin for GNU social
 * Copyright (C) 2015, Enkidu Coop.
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

if (!defined('GNUSOCIAL') || !defined('STATUSNET')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

$dir = dirname(__FILE__);
include_once $dir . '/lib/SocialCapitalIndex.php';

/**
 * Social Capital Plugin
 *
 * @package SocialCapitalPlugin
 * @maintainer Manuel Ortega <manuel@enkidu.coop>
 */
class SocialCapitalPlugin extends Plugin
{

    /**
     * Initializer for this plugin
     *
     * Plugins overload this method to do any initialization they need,
     * like connecting to remote servers or creating paths or so on.
     *
     * @return boolean hook value; true means continue processing, false means stop.
     */
    function initialize()
    {
        return true;
    }

    //function onAutoload($cls)
    //{
    //   $dir = dirname(__FILE__);
    //    switch ($cls)
    //    {
    //        case 'SocialCapitalIndex':
    //            include_once $dir . '/lib/'.$cls.'.php';
    //            return false;
    //        default:
    //            return true;
    //    }
    //}

    function onPluginVersion(array &$versions)
    {
        $versions[] = array(
            'name' => 'SocialCapital',
            'version' => GNUSOCIAL_VERSION,
            'author' => 'Manuel Ortega',
            'homepage' => 'http://bitujo.enkidu.coop',
            // TRANS: Plugin description.
            'rawdescription' => _m('Social capital index')
        );

        return true;
    }

    /**
     * Add paths to the router table
     *
     * Hook for RouterInitialized event.
     *
     * @param URLMapper $m URL mapper
     *
     * @return boolean hook return
     */
    public function onStartInitializeRouter(URLMapper $m)
    {
        $m->connect('socialcapital/index/:id',
                    array(  'action' => 'scindexdetail',
                            'id' => '[0-9]+'
                         ));
        return true;
    }

    function onCheckSchema()
    {
        return true;
    }


    /**
     * Add social capital to the API response
     *
     */

    function onTwitterUserArray($profile, &$twitter_user, $scoped)
    {
        try {
            $sc = SocialCapitalIndex::init($profile->id);
            $index = $sc->index();
            $twitter_user['social_capital'] = $index;
        } catch (Exception $e) {
            $twitter_user['social_capital'] = 'error';
        }

        return true;
    }

    function onEndShowAccountProfileBlock(HTMLOutputter $out, Profile $profile) {
        $user = User::getKV('id', $profile->id);
        if ($user) {

                $this->sc = SocialCapitalIndex::init($user->id);

                $out->elementStart('dl');
                $out->element('dt', array('style' => 'display:inline;'), _m('Social capital index:'));
                $out->element('dd', array('style' => 'display:inline;'), $this->sc->index() );
                $out->elementEnd('dl');
        }
    }

    function onQvitterEndShowHeadElements($action)
    {

        print '<link rel="stylesheet" type="text/css" href="'.$this->path('css/socialcapital.css').'" />'."\n ";

    }

    function onQvitterEndShowScripts($action)
    {
        print '<script type="text/javascript" src="'.$this->path('js/socialcapital.qvitter.js').'"></script>'."\n";
    }

}
