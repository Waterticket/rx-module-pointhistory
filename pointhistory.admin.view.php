<?php
	/**
	 * @class  pointhistoryAdminView
     * @author CONORY (https://xe.conory.com)
	 * @brief The admin view class of the pointhistory module
	 */
	 
	class pointhistoryAdminView extends pointhistory
	{
		/**
		 * @brief Initialization
		 */
		function init()
		{
			Context::set('config', $this->config);
			
			$this->setTemplatePath($this->module_path . 'tpl');
			
			// ���׷��̵� �ʿ�
			if($this->isUpgrade() && $this->act != 'dispPointhistoryAdminUpgrade')
			{
				$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispPointhistoryAdminUpgrade'));
			}
		}

		/**
		 * @brief ȯ�� ����
		 */
		function dispPointhistoryAdminConfig()
		{
			// ���̾ƿ�
			$oLayoutModel = getModel('layout');
			Context::set('layout_list', $oLayoutModel->getLayoutList());
			Context::set('mlayout_list', $oLayoutModel->getLayoutList(0, 'M'));
			
			// ��Ų
			$oModuleModel = getModel('module');
            Context::set('skin_list', $oModuleModel->getSkins($this->module_path));
			Context::set('mskin_list', $oModuleModel->getSkins($this->module_path, 'm.skins'));
			
			$this->setTemplateFile('config');
		}

		/**
		 * @brief �����丮 ���
		 */
		function dispPointhistoryAdminList()
		{
            // �˻� �ɼ�
			$search_option = array('nick_name', getModel('module')->getModuleConfig('member')->identifier, 'content');
            Context::set('search_option', $search_option);
			
			$args = new stdClass;
			
            if(($search_target = Context::get('search_target')) && in_array($search_target, $search_option))
			{
				$args->$search_target = Context::get('search_keyword');
            }
			
			$args->type = Context::get('type');
		    $args->page = Context::get('page');
            $output = executeQueryArray('pointhistory.getPointhistoryMemberList', $args);
			
            Context::set('total_count', $output->page_navigation->total_count);
            Context::set('total_page', $output->page_navigation->total_page);
            Context::set('page', $output->page);
            Context::set('history_list', $output->data);
            Context::set('page_navigation', $output->page_navigation);
			
			$this->setTemplateFile('list');
		}
		
		/**
		 * @brief ��ü ��Ȳ
		 */
		function dispPointhistoryAdminStatus()
		{
		    if($this->config->point_status == 'N')
			{
				return new BaseObject(-1, 'msg_invalid_request');
			}
			
			// ��ü ����Ʈ
			Context::set('total_point', executeQuery('pointhistory.getTodayStatus')->data->point);
			
			$args = new stdClass;
			$args->page = Context::get('page');
			$output = executeQueryArray('pointhistory.getPointhistoryStatusList');
			
            Context::set('total_count', $output->page_navigation->total_count);
            Context::set('total_page', $output->page_navigation->total_page);
            Context::set('page', $output->page);
            Context::set('status_list', $output->data);
            Context::set('page_navigation', $output->page_navigation);
			
			$this->setTemplateFile('status');
		}
		
		/**
		 * @brief ȸ���� ��Ȳ
		 */
		function dispPointhistoryAdminStatusMember()
		{
		    if($this->config->point_status == 'N')
			{
				return new BaseObject(-1, 'msg_invalid_request');
			}
			
            // �˻� �ɼ�
			$search_option = array('nick_name', getModel('module')->getModuleConfig('member')->identifier);
            Context::set('search_option', $search_option);
			
			$args = new stdClass;
			
            if(($search_target = Context::get('search_target')) && in_array($search_target, $search_option))
			{
				$args->$search_target = Context::get('search_keyword');
            }
			
		    $args->page = Context::get('page');
            $output = executeQueryArray('pointhistory.getPointhistoryStatusMemberList', $args);
			
            Context::set('total_count', $output->page_navigation->total_count);
            Context::set('total_page', $output->page_navigation->total_page);
            Context::set('page', $output->page);
            Context::set('status_list', $output->data);
            Context::set('page_navigation', $output->page_navigation);
			
			$this->setTemplateFile('status_member');
		}
		
		/**
		 * @brief ���׷��̵�
		 */
		function dispPointhistoryAdminUpgrade()
		{
			Context::set('max_count', $_SESSION['pointhistory_upgrade']['max_count']);
			
			$this->setTemplateFile('upgrade');
			
			// ���׷��̵� �Ϸ�� �̵�
			if(!$this->isUpgrade())
			{
				$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispPointhistoryAdminConfig'));
			}
		}
	}