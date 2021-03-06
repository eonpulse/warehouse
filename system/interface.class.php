<?php
class pub_interface
{
    /**
     * Массив, содержащий шаблон
     *
     * @access public
     * @var array
     */
    var $template;

    /**
     * Контент
     *
     * @access public
     * @var HTML
     */
    //var $content;

    /**
     * Массив, содержащий шаблон
     *
     * @access public
     * @var array
     */
    var $menu      = array(); //Меню левой части интерфейса, разбитое по блокам, для потсроения
    var $menu_line = array(); //То же самое что и $menu но без разбивки по блокам, что бы осуществлять проверку
    var $node_click = array(); //Альтернатива возможных дейсвтий, вызываемых при клике по ноде
    //var $menu_global_link = 'index.php?action=set_two_menu&leftmenu=';
    var $menu_default = '';
    var $menu_block_curent = '';
    var $menu_block_empty = 'empty';
    var $menu_visible = true;
    var $help_visible = true;
    var $tree;


    /**
     * Конструктор класса
     *
     * @return pub_interface
     */
    function pub_interface()
    {
        global $kernel;

    }

    /**
     * Формирует массив для меню с установленными модулями имеющими административный интерфейс
     *
     * @return array
     */
    function priv_menus_modules_create()
    {
        global $kernel;

		$template = $kernel->pub_template_parse("admin/templates/default/tabsmodules.html");

        $manager_modules = new manager_modules();


		// 1. Узнаем количество модулей
		$modules = $manager_modules->priv_modules_admin_interface_count();

		// 2. Узнаем количество модулей с одной админкой
		$menus = array();
		$vareables_name = array();
		$var_is_curent = 'null';
        foreach ($modules as $module_id => $modul_label)
        {
	    	$module_properties = $manager_modules->return_info_modules($module_id);
	        //$module_properties['properties'] 		= unserialize($module_properties['properties']);
	        //$module_properties['properties_page'] 	= unserialize($module_properties['properties_page']);
	        //$module_properties['acces_label'] 		= unserialize($module_properties['acces_label']);
	        //$module_properties['serialize'] 		= unserialize($module_properties['serialize']);
	        //$module_properties['module_setings'] 	= unserialize($module_properties['module_setings']);

	        switch ($module_properties['type_admin'])
	        {
	            //Когда всего одна админка и не зависит от числа дочерних модулей
	            //всё просто один модуль, один ID
				case 1:
				    $select_curent = "false";
				    if ($module_id == $kernel->pub_section_current_get())
				    {
				        $select_curent = "true";
				        $var_is_curent = "this.mod_".$module_properties['id'];
				    }

	            	$menu = $template['type_admin_1'];
	                $menu = str_replace('%module_name%',        $module_properties['full_name'], $menu);
	                $menu = str_replace('%module_description%', $module_properties['full_name'], $menu);
	                $menu = str_replace('%module_id%',          $module_properties['id'],        $menu);
	                $menu = str_replace('%select_curent%',      $select_curent,                  $menu);
	                $vareables_name[] = "this.mod_".$module_properties['id'];
	                break;

	            //Когда для каждого дочернего модуля своя админка
				case 2:
	            	$menu = $template['type_admin_2'];

	            	//Узнаем, сколько дочерних модулей есть этого модуля, и может
	            	//быть один из них текущий
	            	$points = array();
	                $child_modules = $manager_modules->return_modules($module_id);

	                $select_name = '';
	                $select_handler = 'null';
	                $select_curent = "false";
	                foreach ($child_modules as $child_module_id => $child_module_properties)
	                {

	                    //Данные непосредственно на строчку в подменю, для каждого дочернего модуля
	                	$point = $template['type_admin_2_point'];
	                	$point = str_replace('%module_id%', $child_module_id                   , $point);
	                	$point = str_replace('%caption%'  , $child_module_properties['caption'], $point);
                        $points[] = $point;

	                	//Теперь будем заполнять данные для главного меню, пока  не увидем
	                	//что это под меню является текущим. Если такого не случится, то в главному меню
	                	//будет сразу ссылка на последний пункт под меню
	                    if (($child_module_id  == $kernel->pub_section_current_get()) || (empty($select_name)))
	                    {
                            $select_name = $child_module_properties['caption'];
                            $select_handler = "function(item){this.modules_tab_click(item, null);}";
                            $select_id = $child_module_id;
                            //$module_id = $child_module_id;
	                    }

	                    //А теперь проверим, если это действительно выделенный пункт (а не просто по умолчанию
	                    //то его надо ещё и подсветить
                        if ($child_module_id  == $kernel->pub_section_current_get())
                        {
                            $select_curent = "true";
                            $var_is_curent = "this.mod_".$module_id;
                        }

					}
		            //$select_curent = "false";
	                //$select_name = $module_properties['full_name'];
	                //$select_handler = 'null';

	                $menu = str_replace('%menu_points%'       , implode(",\n", $points) , $menu);
	        		$menu = str_replace('%module_name%'       , $select_name            , $menu);
	                $menu = str_replace('%module_description%', $select_name            , $menu);
	                $menu = str_replace('%select_curent%',      $select_curent          , $menu);
	                $menu = str_replace('%parent_id%'         , $module_id              , $menu);
	                $menu = str_replace('%module_id%'         , isset($select_id)?$select_id:''              , $menu);
	                $menu = str_replace('%handler_group%'     , $select_handler         , $menu);

	                $vareables_name[] = "this.mod_".$module_id;
	                break;
			}
			$menus[] = $menu;
		}

		$html = '';

		$html = $template['begin'];
		$html = str_replace('%variables_name%'  , join(",\n",$vareables_name), $html);
		$html = str_replace('%variables_curent%', $var_is_curent             , $html);
		$html = join(";\n",$menus).';'.$html;

		//$kernel->debug($html);
		return $html;
    }

    /**
     * Добавляет в текущий блок дерево элементов.
     *
     * @param Object $tree Объект с деревом.
     */
    function set_tree(&$tree)
    {
        if (empty($this->menu_block_curent))
            $this->menu_block_curent = $this->menu_block_empty;

        $this->menu[$this->menu_block_curent][$tree->get_id()] = array('type' => 'tree',
                                                        'id' => $tree->get_id(),
                                                        'content' => $tree->get_tree()
                                                        );

        //Зарегестрируем здесь же, все действия кликов по ноде
        $this->node_click[$tree->get_action_click_node()] = 1;

    }


    /********************************************************************
    			                  П Р О В Е Р Е Н О
    *********************************************************************/


	/**
	 * Добавляет к текущему блоку произвольный контент.
	 *
	 * @param HTML $content
	 */
    function set_menu_plain($content)
    {
        if (empty($this->menu_block_curent))
        {
            $this->menu_block_curent = $this->menu_block_empty;
        }

        $id = time();

        $this->menu[$this->menu_block_curent][$id] = array(
            'type' => 'plain',
            'id' => $id,
            'content' => $content
        );

        $this->menu_line[$id] = array(
            'type' => 'plain',
            'id' => $id,
            'content' => $content
        );
    }



    /**
     * Объявляет имя блока меню.
     *
     * Все пункты меню, добавленные после вызова конкретного set_menu_block,
     * будут отнесены именно к этому блоку меню.
     * @param String $name Языковая переменная или непосредственное название блока.
     */
    function set_menu_block($name)
    {
        $this->menu_block_curent = $name;
    }

    /**
     * Объявляет пункт меню по умолчанию
     *
     * @param String $name
     */
    function set_menu_default($name)
    {
        global $kernel;

        $kernel->priv_section_leftmenu_set($name);
        $this->menu_default = $name;
    }


    /**
     * Задаёт элемент левого меню
     *
     * Задаёт пункт левого меню. Его навзвание и ID. Указанное
     * ID будет доступно в функции Start соответсвующего менеджера
     * через функцию pub_section_left_menu()
     * @param string $name Имя меню для администратора
     * @param string $id идентификатор меню
     * @param array $array Массив дополнительных параметров, которые необхдимо добавить к GET запросу
     * @access private
     * @return void
     */

    function set_menu($name = '', $id = '', $array = null)
    {
        if (is_array($array)) {
            $query = array();
        	foreach ($array as $param => $value) {
        		$query[] = $param.'='.$value;
        	}
        }

        if (empty($this->menu_block_curent))
            $this->menu_block_curent = $this->menu_block_empty;

        $this->menu[$this->menu_block_curent][$id] = array('type' => 'menu',
                                                        'id' => $id,
                                                        'content' => $name,
                                                        'query' => ((isset($query))?(implode('&', $query)):(''))
                                                        );
        $this->menu_line[$id] = array('type' => 'menu',
                                    'id' => $id,
                                    'content' => $name,
                                    'query' => ((isset($query))?(implode('&', $query)):(''))
                                    );

    }


    /**
     * Проверяет правильность текущего пункта левого меню
     *
     * Вызывается перед выводом контента всей секции. В случае, если текущем
     * пунктом меню является один из пунктов, которого реалньо нет в меню
     * то он заменяется на пункт меню по умолчанию. Администратор может
     * покинуть секцию находясья в каком-то процессе, и возврат к этому процессу
     * в таком виде, может привести к нежелательным действиям.
     * @access private
     * @return void
     */

    function check_left_element()
    {
        global $kernel;

        $curent = $kernel->pub_section_leftmenu_get();

//        if ((empty($curent)) && (!empty($this->menu_default)))
//            $kernel->priv_section_leftmenu_set($this->menu_default, true);
        //$kernel->debug($curent, true);

        if ((!isset($this->menu_line[$curent])) && (!isset($this->node_click[$curent])))
        {
            if (!empty($this->menu_default))
                $kernel->priv_section_leftmenu_set($this->menu_default, true);
        }
    }


    /**
     * Строит массив блоков меню по по массиву $this->menu
     *
     * В качестве значений массива - непосредственный HTML код, который
     * должн быть вставлен в соответствующий блок меню
     * @return array
     */
    function get_menu($template)
    {

        global $kernel;

        $array_block = array();
        foreach ($this->menu as $name_block => $val)
        {
            //Сначала соберём тот контент, который должен находиться в этом блоке
            $array_menu = array();
            foreach ($val as $id => $elem)
            {
                $one_html = '';
                switch ($elem['type']) {
                    //Лбычный элемент меню
                	case 'menu':
                        if ($id == $kernel->pub_section_leftmenu_get())
                            $one_html = $template["lm_menu_passiv"];
                        else
                            $one_html = $template["lm_menu_activ"];

                        $one_html = str_replace("[#name#]", $elem['content'], $one_html);
                        $one_html = str_replace("[#id#]", $id, $one_html);
                        $one_html = str_replace("%query%", $elem['query'], $one_html);
                		break;

                	case 'tree':
                	    $one_html = $elem['content'];
                		break;
                	case 'plain':
                	    $one_html = $elem['content'];
                		break;
                }
                $array_menu[] = $one_html;
            }

            $html = $template['lm_begin'];
            $html .= join($template['lm_delimiter'], $array_menu);
            $html .= $template['lm_end'];
            $array_block[$name_block] = $html;
        }
//        var_dump($array_block);
//        $kernel->debug($array_block, true);
        return $array_block;
    }


	/**
	 * Создаёт объекты для левого меню
	 *
	 * Создаёт объекты кода, для пострения меню. Построение меню ведеётся по данным массива
	 * $this->menu который заполняется соответсвующими классми разных частей
	 * административного интерфейса
	 * @return HTML
	 */

    function left_menu_construct()
    {

        global $kernel;

        $template = $kernel->pub_template_parse("admin/templates/default/leftmenu.html");

        $html = $template['begin'];

        //Выведем левое меню меню
        $left_menu = '';
        $srcipts = '';

        if (!empty($this->menu))
        {
            //Орпеделим, какоё количество блоков меню нам нужно.
            $i = 0;
            foreach ($this->get_menu($template) as $name_block => $block_content)
            {
                //Код скрипта для панели
                $tmp = $template['panel_add'];
                $tmp = str_replace("[#name_panel#]", $name_block, $tmp);
                $tmp = str_replace("[#div_panel#]", "div_panel_".$i, $tmp);
                $tmp = str_replace("[#id#]", $i, $tmp);
                $srcipts .= $tmp;

//                var_dump($block_content);
//                $kernel->debug($name_block, true);

                //Перед вставкой контента в блок необходимо заменить если там есть пустое
                //имя ДИВ-а для контента
                $block_content = str_replace("[#name_div_content#]", "div_panel_".$i."_content", $block_content);

                $left_menu .= '<div id="div_panel_'.$i.'"><div></div><div id="div_panel_'.$i.'_content">'.$block_content.'</div></div>';
                $i++;
            }
        }

        $html = str_replace("[#script_create_panel#]", $srcipts, $html);
        $html = str_replace("[#left_menu#]", $left_menu, $html);

        //Сюда же пропишем если у нас есть доп параметры в GET запросе
		//Если есть GET запрос, нужно его передать дальше
		$next_get = '';
		$tmp = $kernel->pub_httpget_get();
		if (isset($tmp['action']))
		  unset($tmp['action']);

		if (is_array($tmp))
		{
		    foreach ($tmp as $get_name => $get_value)
		        $next_get[] = $get_name."=".$get_value;

		    $next_get = join("&", $next_get);
		}
		$html = str_replace("[#get_url#]"    , $next_get, $html);

        return $html;

    }

}
?>
