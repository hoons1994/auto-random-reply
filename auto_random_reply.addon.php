<?php //php 문서 시작

	if(!defined('__ZBXE__') && !defined('__XE__')) exit(); //XE가 아닐 경우 작동하지 않음

/**
 * @file auto_random_reply.addon.php
 * @brief 자동 랜덤댓글 애드온
 * @nick_name charmingcolor
 * 선택한 게시판에 등록된 글에 댓글을 랜덤하게 작성해주는 애드온입니다.
 **/

	//애드온이 사용 중일 때만 작동
	if($addon_info->use_addon == 'Y'){
			
		$document_srl = Context::get('document_srl'); //document_srl을 받아옴

		$multi_readed_count = 0; //조회수가 중복으로 증가하지 않도록 하기 위한 변수
		$multi_readed_count = $_SESSION['auto_random_reply_readed_count'][$document_srl]; //생성된 세션의 카운트를 저장함

		if($document_srl && $multi_readed_count != '1') { //문서이고 조회수가 증가하지 않았을 경우에만

			$args->document_srl = $document_srl;
			executeQuery('addons.auto_random_reply.updateReadedCount1', $args);

			$_SESSION['auto_random_reply_readed_count'][$document_srl] = $multi_readed_count + 1; // 동일글 조회수를 올려서 작동 중지 시킴

		}

		if($called_position == 'after_module_proc'){ //해당 모듈이 작동하고 난 후에 작동

			//메세지가 있으면 출력후 패스
			if($_SESSION['addon_auto_random_reply_msg'] && $this->act == 'dispBoardContent'){
				unset($_SESSION['addon_auto_random_reply_msg']); //변수를 해제함
				return;
			}

			//에러시 패스
			if($this->error) return;

			//파일 업로드시
			if($this->act == 'procFileUpload'){

				//업로드시 새문서 체크
				$_SESSION['addon_auto_random_reply_uploadTargetSrl'] = Context::get('uploadTargetSrl') ? false:true;

			}
			
			if( $this->act == 'procBoardInsertDocument' ){
				
				$upload_srl = $_SESSION['addon_auto_random_reply_uploadTargetSrl']; //파일이 업로드 된 정보를 넘김
				unset($_SESSION['addon_auto_random_reply_uploadTargetSrl']); //변수를 해제

				//해당 액션이고 신규 문서이면
				if( $this->act=='procBoardInsertDocument' && (!Context::get('document_srl') || (Context::get('document_srl') && $upload_srl) ) ) $addon_act = 'document';
				elseif( $this->act=='procBoardInsertComment' && (!Context::get('comment_srl') || (Context::get('comment_srl') && $upload_srl) ) ) $addon_act = 'comment';
				else return;
				
				$oLogIfo = Context::get('logged_info'); //로그인 정보를 받아옴

				//최고 관리자이거나 로그인 유저가 아니면 패스
				if(!$oLogIfo->member_srl || ($oLogIfo->is_admin == 'Y' && $addon_info->is_admin == 'N') || $oLogIfo->denied =='Y') return;

				$oMemberModel = &getModel('member');
				$member_info=$oMemberModel->getMemberInfoByMemberSrl($addon_info->member_srl);

				$com_nick = $addon_info->nick_name;


				function make_seed()
				{
					list($usec, $sec) = explode(' ', microtime());
					return (float) $sec + ((float) $usec * 100000);
				}

				while(true){

					mt_srand(make_seed());
					$randomNum = mt_rand(1, 10);

					if($randomNum == 1 && $addon_info->cm1_use == 'Y'){

					$com_ment = $addon_info->comment_ment1;
					break;

					}

					elseif($randomNum == 2 && $addon_info->cm2_use == 'Y'){

						$com_ment = $addon_info->comment_ment2;
						break;

					}

					elseif($randomNum == 3 && $addon_info->cm3_use == 'Y'){

						$com_ment = $addon_info->comment_ment3;
						break;

					}

					elseif($randomNum == 4 && $addon_info->cm4_use == 'Y'){

						$com_ment = $addon_info->comment_ment4;
						break;

					}

					elseif($randomNum == 5 && $addon_info->cm5_use == 'Y'){

						$com_ment = $addon_info->comment_ment5;
						break;

					}

					elseif($randomNum == 6 && $addon_info->cm6_use == 'Y'){

						$com_ment = $addon_info->comment_ment6;
						break;

					}

					elseif($randomNum == 7 && $addon_info->cm7_use == 'Y'){

						$com_ment = $addon_info->comment_ment7;
						break;

					}

					elseif($randomNum == 8 && $addon_info->cm8_use == 'Y'){

						$com_ment = $addon_info->comment_ment8;
						break;

					}

					elseif($randomNum == 9 && $addon_info->cm9_use == 'Y'){

						$com_ment = $addon_info->comment_ment9;
						break;

					}

					elseif($randomNum == 10 && $addon_info->cm10_use == 'Y'){

						$com_ment = $addon_info->comment_ment10;
						break;

					}
				}

				$success = true; //준비 과정을 마쳤을 때


				if($success){
					if($addon_info->member_srl != '') {

						$comObj->member_srl = $member_info->member_srl;
						$comObj->user_name = $member_info->user_name;
						$comObj->user_id = $member_info->user_id;
						$comObj->nick_name = $member_info->nick_name;

					}

					else{

						$comObj->member_srl = $addon_info->member_srl;
						$comObj->user_name = 'random';
						$comObj->user_id = 'random';
						$comObj->nick_name = $com_nick;

					}

					$comObj->email_address = $comObj->homepage = ''; //댓글 작성자의 email_address, homepage, user_id ''로 초기화
					$comObj->module_srl = $this->module_srl;
					$comObj->document_srl = $this->variables['document_srl'];
					$comObj->content = $com_ment;

					$ccComment = &getController('comment');

					$bk_avoidlog = $_SESSION['avoid_log'];
					$_SESSION['avoid_log'] = true;

					$tmpout = $ccComment->insertComment($comObj, true);

					$_SESSION['avoid_log'] = $bk_avoidlog;
					unset($_SESSION['own_comment'][$tmpout->get('comment_srl')]);
					
					// 최신 댓글수를 가져옴
					$oCommentModel = getModel('comment');
					$comment_count = $oCommentModel->getCommentCount($comObj->document_srl);
					$comment_count = max(1, $comment_count);
 
					// 문서의 댓글수 업데이트
					$oDocumentController = getController('document');
					$oDocumentController->updateCommentCount($comObj->document_srl, $comment_count, $comObj->nick_name);

					$com_ment = preg_replace("\r|\n", "", strip_tags($com_ment,'<br>'));
					$com_ment = str_replace('&amp;', '&', $com_ment);

					$_SESSION['addon_auto_random_reply_msg'] = str_replace('"','`',preg_replace('/\<br(\s*)?\/?\>/i', '\n', $com_ment));
				}
			}
		}
	}
?>
