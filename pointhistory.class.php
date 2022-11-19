<?php
	/**
	 * @class  pointhistory
     * @author CONORY (https://xe.conory.com)
	 * @brief The parent class of the pointhistory module
	 */
	
	class pointhistory extends ModuleObject
	{
		public $config = null;
		
		// ��ġ Ʈ����
		private $triggers = array(
			array('point.setPoint', 'pointhistory', 'controller', 'triggerSetPoint', 'after'),
			array('moduleHandler.init', 'pointhistory', 'controller', 'triggerModuleHandler', 'after'),
			array('member.deleteMember', 'pointhistory', 'controller', 'triggerDeleteMember', 'after'),
		);
		
		// ���� Ʈ���� for ������
		private $delete_triggers = array(
			array('moduleObject.proc', 'pointhistory', 'controller', 'triggerBeforeModuleObject', 'before'),
			array('moduleObject.proc', 'pointhistory', 'controller', 'triggerAfterModuleObject', 'after'),
			array('file.downloadFile', 'pointhistory', 'controller', 'triggerDownloadFile', 'after'),
		);
		
		function __construct()
		{
			$this->config = $this->getConfig();
			
			if(Context::get('module') == 'admin')
			{
				// �����丮 ��� �ڵ� ����
				if($this->config->delete_record_auto)
				{
					$args = new stdClass;
					$args->regdate_less = date('YmdHis', strtotime(sprintf('-%s day', $this->config->delete_record_auto)));
					executeQuery('pointhistory.deletePointhistoryLogLess', $args);
				}
				
				// ����Ʈ ��Ȳ ��� �ڵ� ����
				if($this->config->delete_status_record_auto)
				{
					$args = new stdClass;
					$args->update_less = date('YmdHis', strtotime(sprintf('-%s day', $this->config->delete_status_record_auto)));
					executeQuery('pointhistory.deletePointhistoryStatusLess', $args);
					executeQuery('pointhistory.deletePointhistoryMemberStatusLess', $args);
				}
			}
		}
		
		/**
		 * @brief ��� ��ġ
		 */
		function moduleInstall()
		{
            $oModuleModel = getModel('module');
            $oModuleController = getController('module');
			
			return new BaseObject();
		}

		/**
		 * @brief ������Ʈ üũ
		 */
		function checkUpdate()
		{
            $oDB = DB::getInstance();
            $oModuleModel = getModel('module');
			
			// Ʈ��Ŀ ��ġ
			foreach($this->triggers as $trigger)
			{
				if(!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]))
				{
					return true;
				}
			}
			
			// Ʈ��Ŀ ���� for ������
			foreach($this->delete_triggers as $trigger)
			{
				if($oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]))
				{
					return true;
				}
			}
			
			// ���������� ����� ������Ʈ ����
			if(FileHandler::exists('modules/pointhistory/schemas/pointhistory.xml'))
			{
				return true;
			}
			
			// ���׷��̵� �ʿ�
			return $this->isUpgrade();
		}

		/**
		 * @brief ������Ʈ
		 */
		function moduleUpdate()
		{
            $oDB = DB::getInstance();
            $oModuleModel = getModel('module');
            $oModuleController = getController('module');
			
			// Ʈ��Ŀ ��ġ
			foreach($this->triggers as $trigger)
			{
				if(!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]))
				{
					$oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
				}
			}
			
			// Ʈ��Ŀ ���� for ������
			foreach($this->delete_triggers as $trigger)
			{
				if($oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]))
				{
					$oModuleController->deleteTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
				}
			}
			
			// ���������� ����� ������Ʈ ����
			if(FileHandler::exists('modules/pointhistory/schemas/pointhistory.xml'))
			{
				return new BaseObject(-1, 'msg_pointhistory_overlay_directory');
			}
			
			// ���׷��̵� �ʿ�
			if($this->isUpgrade())
			{
				return new BaseObject(-1, 'msg_need_pointhistory_upgrade');
			}
			
			return new BaseObject(0, 'success_updated');
		}
	
		/**
		 * @brief ��� ����
		 */
		function moduleUninstall()
		{
			$oDB = DB::getInstance();
			$oModuleModel = getModel('module');
			$oModuleController = getController('module');
			
			// Ʈ��Ŀ ����
			foreach($this->triggers as $trigger)
			{
				if($oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]))
				{
					$oModuleController->deleteTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
				}
			}
			
			return new BaseObject();
		}
		
		/**
		 * @brief ĳ������ �����
		 */
		function recompileCache()
		{
			// �� ����Ʈ �� ���
			if($this->config->point_status == 'Y')
			{
				$args = new stdClass;
				
				// ������ ����Ʈ�� ����
				if($this->config->status_except_admin == 'Y')
				{
					$args->is_admin = 'N';
				}
				
				$args->point = executeQuery('pointhistory.getPointAll', $args)->data->point_all;
				
				if(executeQuery('pointhistory.getTodayStatus')->data->count)
				{
					executeQuery('pointhistory.updateTodayStatus', $args);
				}
				else
				{
					executeQuery('pointhistory.insertTodayStatus', $args);
				}
			}
		}
		
 		/**
		 *@brief ����
		 **/
        function getConfig() 
		{
			$config = getModel('module')->getModuleConfig('pointhistory');
			
			if(!$config->member_menu_name || $config->member_menu_name == Context::getLang('point_history_list'))
			{
				$config->member_menu_name = Context::getLang('point_history_list');
			}
			if(!$config->increase_name || $config->increase_name == Context::getLang('accumulate'))
			{
				$config->increase_name = Context::getLang('accumulate');
			}
			if(!$config->decrease_name || $config->decrease_name == Context::getLang('use'))
			{
				$config->decrease_name = Context::getLang('use');
			}
			
			$config->delete_record_leave = $config->delete_record_leave ?: 'Y';
			$config->delete_record_auto = $config->delete_record_auto ?: 0;
			$config->add_member_menu = $config->add_member_menu ?: 'Y';
			$config->point_unit_char = $config->point_unit_char ?: 'P';
			$config->skin = $config->skin ?: 'default';
			$config->mskin = $config->mskin ?: 'default';
			
			$config->point_status = $config->point_status ?: 'Y';
			$config->status_except_admin = $config->status_except_admin ?: 'Y';
			$config->delete_status_record_auto = $config->delete_status_record_auto ?: 0;
			
            return $config;
        }
		
 		/**
		 *@brief ���׷��̵� �ʿ� ����
		 **/
        function isUpgrade()
		{
            $oDB = DB::getInstance();
			
			// ���̺� ���� �ʿ�
			if($oDB->isTableExists('pointhistory'))
			{
				return true;
			}
			
			return false;
		}
	}