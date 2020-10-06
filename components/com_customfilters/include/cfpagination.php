<?php
/**
 *
 * Customfilters pagination class
 *
 * @package		customfilters
 * @author		Sakis Terz
 * @link		http://breakdesigns.net
 * @copyright	Copyright (c) 2012-2018 breakdesigns.net. All rights reserved.
 * @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *				customfilters is free software. This version may have been modified
 *				pursuant to the GNU General Public License, and as distributed
 *				it includes or is derivative of works licensed under the GNU
 *				General Public License or other free or open source software
 *				licenses.
 * @version $Id: cfpagination.php 1 2015-03-03 18:50:00Z sakis $
 */

defined('_JEXEC') or die;
jimport('joomla.html.pagination');

/**
 * Класс, расширяющий JPagination
 * Поскольку VM не позволяет использовать JPagination по умолчанию в макете - следует расширить
 * The class that extends the JPagination
 * Since VM does not allow to use the default JPagination in the layout - Should be extended
 *
 * @package customfilters
 * @author Sakis Terz
 * @since 3.9
 */
class cfPagination extends JPagination
{
    /**
     * Префикс, используемый для переменных запроса
     * Prefix used for request variables.
     * @var    integer
     * @since  1.6
     */
    public $prefix = null;

	protected $menuparams;
	protected $_perRow;

    /**
     *
     * @param int $total
     * @param int $limitstart
     * @param int $limit
     * @param int $perRow
     * @throws Exception
     * @since 3.9
     */
	public function __construct($total, $limitstart, $limit, $perRow=3)
	{
        $this->app = \Joomla\CMS\Factory::getApplication();
        $this->prefix = 'com_customfilters';
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $option = $jinput->get('option', '', 'cmd');
        $current_itemId = $jinput->get('Itemid', '0', 'int');
        $this->menuparams = cftools::getMenuparams();
        $this->cfinputs = CfInput::getInputs();
        $this->_perRow = $this->menuparams->get('prod_per_row', 3);

//        $this->prefix .= 'lang=' . $this->app->input->get('lang' , null  ) . '&' ;






		parent::__construct($total, $limitstart, $limit);

		//ItemId
        //valid also to the ajax requests
		if($option=='com_customfilters' && !empty($current_itemId)){
            $itemId=$current_itemId;
        }

		if(!empty($itemId))$this->setAdditionalUrlParam('Itemid',$itemId);
		$vars=$this->getVarsArray();
		if(count($vars)>0){
			$vars['option']= 'com_customfilters';
			$vars['view']= 'products';
		}
		foreach ($vars as $key=>$var){
			if(is_array($var)){
				for($i=0; $i<count($var); $i++){
					$var_name=$key."[$i]";
					if(isset($var[$i]))$this->setAdditionalUrlParam($var_name,$var[$i]);
				}
			}else $this->setAdditionalUrlParam($key,$var);

		}
		$this->setAdditionalUrlParam('tmpl',''); //reset the tmpl as it comes from the ajax requests
	}

	/**
	 * (non-PHPdoc)
	 * @see JPagination::getLimitBox()
     * @since 3.9
	 */
	public function getLimitBox()
	{

		$url=$this->getStatURI();
		$url=JRoute::_($url);

		$myURI=JURI::getInstance($url);
		//if(!empty($itemId))$myURI->setVar('Itemid', $itemId);
		if($myURI->getQuery())$wildcard='&';
		else $wildcard='?';
		$url.=$wildcard;

		$limits = array ();
		$pagination_seq=$this->menuparams->get('pagination_list_sequence','12,24,36,48,60,72');
		$pagination_seq_array=explode(',', $pagination_seq);

		// Generate the options list.
		foreach ($pagination_seq_array as $seq) {
			$seq=(int)trim($seq);
			if($seq< $this->_perRow)continue; //it should be higher than the per row elements
			$limits[] = JHtml::_('select.option', 'limit='.$seq,$seq);
		}

		$js='onchange="window.top.location=\''.$url.'\'+this.options[this.selectedIndex].value"';
		$selected ='limit='.$this->limit;
		$html = JHtml::_('select.genericlist',  $limits,  'limit', 'class="inputbox" size="1"'.$js, 'value', 'text', $selected);

		return $html;
	}

	/**
     * Создает статическую часть uri, куда будет добавлен предел var
	 * Creates the static part of the uri where the limit var will be added
	 *
	 * @package customfilters
	 * @since 1.0
	 * @author Sakis Terz
	 */
	public function getStatURI()
    {
        $jinput = JFactory::getApplication()->input;
        $query_ar = $this->getVarsArray();
        //print_r($query_ar);
        if (count($query_ar) > 0)
        {
            $query_ar['option'] = 'com_customfilters';
            $query_ar['view'] = $jinput->getCmd('view', '');
        }
        $u = JFactory::getURI();
        $query = $u->buildQuery($query_ar);
        $uri = 'index.php?' . $query;




        try
        {
            // Code that may throw an Exception or Error.

//            throw new Exception('Code Exception ' . __FILE__ . ':' . __LINE__);
        } catch (Exception $e)
        {
            // Executed only in PHP 5, will not be reached in PHP 7
            echo 'Выброшено исключение: ', $e->getMessage(), "\n";
            echo '<pre>'; print_r($e); echo '</pre>' . __FILE__ . ' ' . __LINE__;
            die(__FILE__ . ' ' . __LINE__);
        }

//        echo '<pre>'; print_r($uri); echo '</pre>' . __FILE__ . ' ' . __LINE__;
//        die(__FILE__ . ' ' . __LINE__);


        return $uri;
    }

    /**
     * Creates and array with the vars that will be used
     *
     * @return array
     * @throws Exception
     * @since 3.9
     */
	public function getVarsArray()
	{
		$jinput=JFactory::getApplication()->input;
		$query_ar=array();
		$inputs=CfInput::getInputs();
		$query_ar=CfOutput::getOutput($inputs);

		$itemId=$jinput->get('Itemid',0,'int');
		if(!empty($itemId))$query_ar['Itemid']=$itemId;
		//print_r($query_ar);
		return $query_ar;
	}

    /**
     * Get the Order By Select List
     * Overrides the function originaly written by Kohl Patrick (Virtuemart parent class)
     *
     * @param int        The category id
     *
     * @return    array    the orderBy HTML List and the manufacturers list
     * @throws Exception
     * @since 3.9
     **@author    Sakis Terz
     * @access    public
     */
	public function getOrderByList($virtuemart_category_id=false, $default_order_by, $order_by, $order_dir='ASC')
	{
	    $app = JFactory::getApplication() ;
	    $jinput=$app->input;

	    //load the virtuemart language files
	    if(method_exists('VmConfig', 'loadJLang'))VmConfig::loadJLang('com_virtuemart',true);
	    else{
	        $language=JFactory::getLanguage();
	        $language->load('com_virtuemart');
	    }

	    $orderTxt ='';
	    $orderByLinks='';
	    $first_optLink='';

	    $orderDirTxt=JText::_('COM_VIRTUEMART_'.$order_dir);

	    /* order by link list*/
	    $fields = VmConfig::get('browse_orderby_fields');

	    if(!in_array($default_order_by, $fields))$fields[]=$default_order_by;

	    if (count($fields)>0) {
	        foreach ($fields as $field) {
                // indicates if this is the current option
                $stripped_field = str_replace('`', '', $field);
                if ($field == $order_by || $stripped_field == $order_by) {
                    $selected = true;
                }
                else {
                    $selected = false;
                }

	            //remove the dot from the string in order to use it as lang string
	            $dotps = strrpos($field, '.');
	            if($dotps!==false){
	                $prefix = substr ($field, 0, $dotps + 1);
				    $fieldWithoutPrefix = substr ($field, $dotps + 1);
	            } else {
	                $prefix = '';
	                $fieldWithoutPrefix = $field;
	            }
	            $fieldWithoutPrefix_tmp=$fieldWithoutPrefix;

	            $text = JText::_('COM_VIRTUEMART_'.strtoupper (str_replace(array(',',' '),array('_',''),$fieldWithoutPrefix)));
	            $link = $this->getOrderURI($fieldWithoutPrefix_tmp, $selected, $order_dir);
	            if(!$selected) {
	                $orderByLinks .='<div><a title="'.$text.'" href="'.$link.'" rel="nofollow">'.$text.'</a></div>';
	            }
	            else {
	                $first_optLink='<div class="activeOrder"><a title="'.$orderDirTxt.'" href="'.$link.'" rel="nofollow">'.$text.' '.$orderDirTxt.'</a></div>';
	            }
	        }
	    }

	    //format the final html
	    $orderByHtml='<div class="orderlist">'.$orderByLinks.'</div>';

	    $orderHtml ='
		<div class="orderlistcontainer">
			<div class="title">'.JText::_('COM_VIRTUEMART_ORDERBY').'</div>'
				    .$first_optLink
				    .$orderByHtml
				    .'</div>';

	    //in case of ajax we want the script to be triggered after the results loading
	    $orderHtml .="
			<script type=\"text/javascript\">
		jQuery('.orderlistcontainer').hover(
		function() { jQuery(this).find('.orderlist').stop().show()},
		function() { jQuery(this).find('.orderlist').stop().hide()});
		</script>";

	    return array('orderby'=>$orderHtml, 'manufacturer'=>'');
	}

    /**
     * Создает href, в котором каждый параметр "порядок по" должен указывать на
     * Creates the href in which each "order by" option should point to
     *
     * @return    String    The URL
     * @throws Exception
     * @since    1.0
     * @author    Sakis Terz
     */
    private function getOrderURI($orderBy, $selected = false, $orderDir = 'ASC')
    {
        $u = JFactory::getURI();
        $input = JFactory::getApplication()->input;
        $Itemid = $input->get('Itemid');

        /*
         * get the inputs
         * these are validated and sanitized
         */
        $input = CfInput::getInputs();

        /*
         * Generate the output vars
         */
        $output = CfOutput::getOutput($input);

        $output['option'] = 'com_customfilters';
        $output['view'] = 'products';

        if (isset($Itemid)) $output['Itemid'] = (int)$Itemid;

        //add order by var in the query
        $output['orderby'] = $orderBy;
        //if selected add the order Direction
        if ($selected and $orderDir == 'ASC') $output['order'] = 'DESC';
        else $output['order'] = 'ASC';

        $query = $u->buildQuery($output);
        $uri = 'index.php?' . $query;

//        echo'<pre>';print_r( $uri );echo'</pre>'.__FILE__.' '.__LINE__;
//        echo'<pre>';print_r( JRoute::_($uri) );echo'</pre>'.__FILE__.' '.__LINE__;
//        die(__FILE__ .' '. __LINE__ );


        return JRoute::_($uri);
    }

    /**
     * Create and return the pagination page list string, ie. Previous, Next, 1 2 3 ... x.
     *
     * @return  string  Pagination page list string.
     *
     * @since   1.5
     */
    public function getPagesLinks()
    {
         

        // Build the page navigation list.
        $data = $this->_buildDataObject();

//        echo'<pre>';print_r( $data );echo'</pre>'.__FILE__.' '.__LINE__;
//        die(__FILE__ .' '. __LINE__ );


        $list           = array();
        $list['prefix'] = $this->prefix;

        $itemOverride = false;
        $listOverride = false;

        $chromePath = JPATH_THEMES . '/' . $this->app->getTemplate() . '/html/pagination.php';

        
        if (file_exists($chromePath))
        {
            include_once $chromePath;

            /*
             * @deprecated 4.0 Item rendering should use a layout
             */
            if (function_exists('pagination_item_active') && function_exists('pagination_item_inactive'))
            {
                \JLog::add(
                    'pagination_item_active and pagination_item_inactive are deprecated. Use the layout joomla.pagination.link instead.',
                    \JLog::WARNING,
                    'deprecated'
                );

                $itemOverride = true;
            }





            /*
             * @deprecated 4.0 The list rendering is now a layout.
             * @see Pagination::_list_render()
             */
            if (function_exists('pagination_list_render'))
            {
                \JLog::add('pagination_list_render is deprecated. Use the layout joomla.pagination.list instead.', \JLog::WARNING, 'deprecated');
                $listOverride = true;
            }
        }

        // Build the select list
        if ($data->all->base !== null)
        {
            $list['all']['active'] = true;
            $list['all']['data']   = $itemOverride ? pagination_item_active($data->all) : $this->_item_active($data->all);
        }
        else
        {
            $list['all']['active'] = false;
            $list['all']['data']   = $itemOverride ? pagination_item_inactive($data->all) : $this->_item_inactive($data->all);
        }

        if ($data->start->base !== null)
        {
            $list['start']['active'] = true;
            $list['start']['data']   = $itemOverride ? pagination_item_active($data->start) : $this->_item_active($data->start);
        }
        else
        {
            $list['start']['active'] = false;
            $list['start']['data']   = $itemOverride ? pagination_item_inactive($data->start) : $this->_item_inactive($data->start);
        }

        if ($data->previous->base !== null)
        {
            $list['previous']['active'] = true;
            $list['previous']['data']   = $itemOverride ? pagination_item_active($data->previous) : $this->_item_active($data->previous);
        }
        else
        {
            $list['previous']['active'] = false;
            $list['previous']['data']   = $itemOverride ? pagination_item_inactive($data->previous) : $this->_item_inactive($data->previous);
        }

        // Make sure it exists
        $list['pages'] = array();

        foreach ($data->pages as $i => $page)
        {
            if ($page->base !== null)
            {
                $list['pages'][$i]['active'] = true;
                $list['pages'][$i]['data']   = $itemOverride ? pagination_item_active($page) : $this->_item_active($page);
            }
            else
            {
                $list['pages'][$i]['active'] = false;
                $list['pages'][$i]['data']   = $itemOverride ? pagination_item_inactive($page) : $this->_item_inactive($page);
            }
        }

        if ($data->next->base !== null)
        {
            $list['next']['active'] = true;
            $list['next']['data']   = $itemOverride ? pagination_item_active($data->next) : $this->_item_active($data->next);
        }
        else
        {
            $list['next']['active'] = false;
            $list['next']['data']   = $itemOverride ? pagination_item_inactive($data->next) : $this->_item_inactive($data->next);
        }

        if ($data->end->base !== null)
        {
            $list['end']['active'] = true;
            $list['end']['data']   = $itemOverride ? pagination_item_active($data->end) : $this->_item_active($data->end);
        }
        else
        {
            $list['end']['active'] = false;
            $list['end']['data']   = $itemOverride ? pagination_item_inactive($data->end) : $this->_item_inactive($data->end);
        }

        if ($this->total > $this->limit)
        {
            return $listOverride ? pagination_list_render($list) : $this->_list_render($list);
        }
        else
        {
            return '';
        }
    }

    /**
     * Create and return the pagination data object.
     *
     * @return  \stdClass  Pagination data object.
     *
     * @since   1.5
     */
    protected function _buildDataObject()
    {
        $data = new \stdClass;

        // Build the additional URL parameters string.
        $params = '';

        // build a "limit" string
        $limitString = '&limit=' . $this->limit;

        

//        $params .= 'index.php?' ;

        if (!empty($this->additionalUrlParams))
        {
            foreach ($this->additionalUrlParams as $key => $value)
            {
//                $dataParams[] = $key . '=' . $value ;
                 $params .= '&' . $key . '=' . $value;
            }
//            $params .= implode('&' , $dataParams ) ;

        }

        
        


        $data->all = new JPaginationObject(\JText::_('JLIB_HTML_VIEW_ALL'), $this->prefix);

        if (!$this->viewall)
        {
            $data->all->base = '0';
            $data->all->link = \JRoute::_($params . '&' . $this->prefix . 'limitstart=');
        }

        // Set the start and previous data objects.
        $data->start    = new JPaginationObject(\JText::_('JLIB_HTML_START'), $this->prefix);
        $data->previous = new JPaginationObject(\JText::_('JPREV'), $this->prefix);

        if ($this->pagesCurrent > 1)
        {
            $page = ($this->pagesCurrent - 2) * $this->limit;



            $this->hideEmptyLimitstart = 0 ;
            if ($this->hideEmptyLimitstart)
            {
                $data->start->link = \JRoute::_($params . '&' . $this->prefix . 'limitstart=0');
            }
            else
            {
                $data->start->link = \JRoute::_($params . '&' . $this->prefix /*. 'limitstart=0'*/);

                $is_customfield = strpos( $data->start->link , 'custom_f_');
                if ( is_int( $is_customfield ) )
                {
                    $data->start->link = substr( $data->start->link , 0 , ( strlen($data->start->link) - strlen("&lang=ru") -4 )   ) ;

                }#END IF
            }

            $data->start->base    = '0';
            $data->previous->base = $page;

            $this->hideEmptyLimitstart = true ; 
            





            if ($page == 0 && $this->hideEmptyLimitstart)
            {
                $data->previous->link = $data->start->link;


            }
            else
            {
                $data->previous->link = \JRoute::_($params . '&' . $this->prefix /*. 'limitstart=' . $page*/) . ($page == 0 ? null :  '?start=' . $page )  ;
            }
        }





        // Set the next and end data objects.
        $data->next = new JPaginationObject(\JText::_('JNEXT'), $this->prefix);
        $data->end  = new JPaginationObject(\JText::_('JLIB_HTML_END'), $this->prefix);

        $lang = 'lang=' . $this->app->input->get('lang' , null  ) ;
        $params .= '&'.$lang ;
        if ($this->pagesCurrent < $this->pagesTotal)
        {
            $next = $this->pagesCurrent * $this->limit;
            $end  = ($this->pagesTotal - 1) * $this->limit;

            $data->next->base = $next;
            $data->next->link = \Joomla\CMS\Router\Route::_( $params . '&' . $this->prefix /*. 'limitstart=' . $next . $limitString */) ;

            $data->next->link .= (stristr( $data->next->link, '?')?'&':'?') . 'start=' . $next  ;




            $data->end->base  = $end;
            $data->end->link  = \Joomla\CMS\Router\Route::_( $params . '&' . $this->prefix /*. 'limitstart=' . $end . $limitString*/   )    ;
            $data->end->link .= ( stristr( $data->end->link , '?') ? '&' : '?' ) . 'start=' . $end  ;
        }




        $data->pages = array();
        $stop        = $this->pagesStop;

        for ($i = $this->pagesStart; $i <= $stop; $i++)
        {
            $offset = ($i - 1) * $this->limit;

            $data->pages[$i] = new JPaginationObject( $i, $this->prefix );

            if ($i != $this->pagesCurrent || $this->viewall)
            {
                $data->pages[$i]->base = $offset;

                if ($offset === 0 && $this->hideEmptyLimitstart)
                {
                    $data->pages[$i]->link = $data->start->link;
                }
                else
                {
                    $data->pages[$i]->link = \JRoute::_($params . '&' . $this->prefix /*. 'limitstart=' . $offset*/ )  ;
                    $data->pages[$i]->link .= ( stristr( $data->pages[$i]->link , '?') ? '&' : '?' ) . 'start=' . $offset ;
                }
            }
            else
            {
                $data->pages[$i]->active = true;
            }
        }
//        echo'<pre>';print_r( $data );echo'</pre>'.__FILE__.' '.__LINE__;
//        die(__FILE__ .' '. __LINE__ );
        return $data;
    }

}