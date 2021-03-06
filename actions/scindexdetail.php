<?php
/**
 * Give a warm greeting to our friendly user
 *
 * PHP version 5
 *
 * @category Sample
 * @package  StatusNet
 * @author   Evan Prodromou <evan@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link     http://status.net/
 *
 * StatusNet - the distributed open-source microblogging tool
 * Copyright (C) 2009, StatusNet, Inc.
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

/**
 * Give a warm greeting to our friendly user
 *
 * This sample action shows some basic ways of doing output in an action
 * class.
 *
 * Action classes have several output methods that they override from
 * the parent class.
 *
 * @category Sample
 * @package  StatusNet
 * @author   Evan Prodromou <evan@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link     http://status.net/
 */
class SCIndexDetailAction extends Action
{
    var $user = null;
    var $gc   = null;

    /**
     * Take arguments for running
     *
     * This method is called first, and it lets the action class get
     * all its arguments and validate them. It's also the time
     * to fetch any relevant data from the database.
     *
     * Action classes should run parent::prepare($args) as the first
     * line of this method to make sure the default argument-processing
     * happens.
     *
     * @param array $args $_REQUEST args
     *
     * @return boolean success flag
     */
    function prepare(array $args=array())
    {
        parent::prepare($args);

        $this->user = User::getKV('id', $this->arg('id'));

        $this->profile = $this->user->getProfile();

        $this->sc = SocialCapitalIndex::init($this->arg('id'));

        return true;
    }

    /**
     * Handle request
     *
     * This is the main method for handling a request. Note that
     * most preparation should be done in the prepare() method;
     * by the time handle() is called the action should be
     * more or less ready to go.
     *
     * @param array $args $_REQUEST args; handled in prepare()
     *
     * @return void
     */
    function handle($args)
    {
        parent::handle($args);

        $this->showPage();
    }

    /**
     * Title of this page
     *
     * Override this method to show a custom title.
     *
     * @return string Title of the page
     */
    function title()
    {
        if (empty($this->user)) {
            // TRANS: Page title for sample plugin.
            return _m('No hay usuario');
        } else {
            // TRANS: Page title for sample plugin. %s is a user nickname.
            return sprintf(_m('Detalle para el usuario: %s'), $this->user->nickname);
        }
    }

    /**
     * Show content in the content area
     *
     * The default StatusNet page has a lot of decorations: menus,
     * logos, tabs, all that jazz. This method is used to show
     * content in the content area of the page; it's the main
     * thing you want to overload.
     *
     * This method also demonstrates use of a plural localized string.
     *
     * @return void
     */
    function showContent()
    {
        if (empty($this->user)) {
            $this->element('p', array('class' => 'greeting'),
                           // TRANS: Message in sample plugin.
                           _m('Hello, stranger!'));
        } else {

            $this->element('h3', array('class' => 'greeting'), 'Emisión/Comunicación (+2)');


            $this->element('p', array('class' => 'greeting'),
                           // TRANS: Message in sample plugin. %s is a user nickname.
                           sprintf(_m('%s ha publicado %d mensajes y ha compartido (RTs) %d.'), $this->profile->fullname, $this->sc->ttl_notices, $this->sc->ttl_shares));

            $this->element('h3', array('class' => 'greeting'), 'Adhesión (+5)');

            $this->element('p', array('class' => 'greeting'),
                           // TRANS: Message in sample plugin. %s is a user nickname.
                           sprintf(_m('%s tiene %d suscriptores, sus noticias han sido marcadas como favoritas en %d ocasiones.'), $this->profile->fullname, $this->sc->ttl_followers, $this->sc->ttl_faved));

            $this->element('h3', array('class' => 'greeting'), 'Participación (+8)');

            $this->element('p', array('class' => 'greeting'),
                           // TRANS: Message in sample plugin. %s is a user nickname.
                           sprintf(_m('%s ha sido mencionado en %d ocasiones y está subscrito a %d usuarios. Sus publicaciones han sido compartidas %d veces.'), $this->profile->fullname, $this->sc->ttl_mentions, $this->sc->ttl_following, $this->sc->ttl_shared));

            $this->element('h3', array('class' => 'greeting'), 'Interacción (+50)');

            $this->element('p', array('class' => 'greeting'),
                           // TRANS: Message in sample plugin. %s is a user nickname.
                           sprintf(_m('%s ha agregado %d posts a la conversación.'), $this->profile->fullname, $this->sc->ttl_noticesFromBlog));

            $this->element('p', array('class' => 'greeting'),
                           // TRANS: Message in sample plugin. %s is a user nickname.
                           sprintf(_m('El Social Capital Index de %s es: %.2f'), $this->profile->fullname, $this->sc->index()));

        }
    }

    /**
     * Return true if read only.
     *
     * Some actions only read from the database; others read and write.
     * The simple database load-balancer built into StatusNet will
     * direct read-only actions to database mirrors (if they are configured),
     * and read-write actions to the master database.
     *
     * This defaults to false to avoid data integrity issues, but you
     * should make sure to overload it for performance gains.
     *
     * @param array $args other arguments, if RO/RW status depends on them.
     *
     * @return boolean is read only action?
     */
    function isReadOnly($args)
    {
        return false;
    }
}
