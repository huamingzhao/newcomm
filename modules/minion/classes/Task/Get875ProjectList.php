<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Help task to display general instructons and list all tasks
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) 2009-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Task_Get875ProjectList extends Minion_Task{

	protected function _execute(array $params){
		#php shell php minion --task=Get875ProjectList
		 self::_get875List();
	
	}
	/**
	 * 获取875数据
	 * @author 嵇烨
	 */
	protected function  _get875List(){
		#获取875的数据
		$project_875_model = ORM::factory("Project");
		#875总数
		$count = $project_875_model->where("project_source","=",intval(2))->where("project_status","=",intval(2))->where("project_temp_status",'=',intval(2))->count_all();
                
		for ($i=0;$i<$count/50;$i++){
			#每次拿取20条数据
			$model2 = $project_875_model->where("project_source","=",intval(2))->where("project_status",'=',intval(2))->where("project_temp_status",'=',intval(2))->limit(50)->offset($i)->find_all();
			self::do_list($model2,$i);
		}
	}

	protected  function do_list($obj_data,$i){
		$Service_Api_Basic =  new Service_Api_Basic();
		#调用接口并取得数据
		foreach ($obj_data as $key=>$val){ 
			//echo $val->project_brand_name;
                    if(!$val->project_brand_name) continue;
			$json_data = $Service_Api_Basic->getApiReturn("http://man.875.cn/rest_project/postProjectExactList",array("pro_name"=>$val->project_brand_name,"limit"=>intval(50),"offset"=>$i));
			echo "<pre>"; print_R($json_data);exit;
			#处理返回的数据
           echo "正在处理项目：".$val->project_brand_name."\n";
			if(!empty($json_data['msg'])){
				#处理图片
				if(isset($json_data['msg'][0]) && !empty($json_data['msg'][0])){
						#处理小图片
						if($json_data['msg'][0]['pro_pic_show']){
                                                        echo "875 返回下图路径：{$json_data['msg'][0]['pro_pic_show']} \n";
							$bool_small_imge = self::do_uplode_project_image($json_data['msg'][0], intval(5));
                                                        if($bool_small_imge)
                                                            echo "小图已处理\n"; 
						}
						if($bool_small_imge == true){
							#修改项目推广语
							self::_do_update_project_advert(array('project_id'=>$val->project_id,"project_advert"=>$json_data['msg'][0]['pro_define']));
                                                        echo "广告语已处理\n"; 
						}
                                                
					}
                                }else{
                                   echo "875 未返回结果\n"; 
                                }
			}
		}
		#处理项目图片上传
		protected function do_uplode_project_image($arr_data,$type){
			$bool = false;
			$data = array();
			if($arr_data){
				#判断数据是不是存在
				$arr_project_data = ORM::factory("Project")->where("project_brand_name",'=',$arr_data['pro_name'])->where("project_source","=",intval(2))->where("project_status",'=',intval(2))->where("project_temp_status",'=',intval(2))->find()->as_array();
				#开始模拟数据上传图片
				if($arr_project_data['project_id'] > intval(0)){
					//上传到服务器
					$files= array();
					$files['project_875_image']['tmp_name'] = $arr_data['pro_pic_show'];
					$files['project_875_image']['size']='120000';
					$files['project_875_image']['name']= date("Y-m-d").'.jpg';
					#判断图片是什么格式
					$str_type = "image/jpeg";
					if(strstr(".jpg",$arr_data['pro_pic_show']) == true){
						$str_type = "image/jpeg";
					}elseif(strstr(".png",$arr_data['pro_pic_show']) == true){
						$str_type = "image/png";
					}elseif(strstr(".gif",$arr_data['pro_pic_show']) == true){
						$str_type = "image/gif";
					}
					$files['project_875_image']['type']=$str_type;
					$files['project_875_image']['error']='0';
                                        if($files['project_875_image']['tmp_name'] && project::checkProLogo($files['project_875_image']['tmp_name'])) {
                                             echo "处理图片{$files['project_875_image']['tmp_name']}\n"; 
                                            $size = getimagesize($files['project_875_image']['tmp_name']);
                                        }else{
                                            echo "图片不存在\n";
                                            return false;
                                        }
					$w=$size[0];$h=$size[1];
					try {
						$img = common::uploadPic($files,'project_875_image',array(array($w,$h)));
					}catch (Error $e){
						self::_get875List();
					}
					if($img['error'] == ""){
						#图片入库
						$data['project_id'] = $arr_project_data['project_id'];
						$data['img'] = $img['path'];
						$bool = self::_do_check_image($data,$type);
					}
				}
			}
			return $bool;
		}
		/**
		 * 检查图片 并入库
		 * @param unknown $data
		 * @param unknown $type
		 * @return boolean
		 */
		protected  function _do_check_image($data,$type){
			$bool = false;
			if($data && $type){
				#查找数据
				$project_model=ORM::factory('Projectcerts')->where('project_id',"=",intval($data['project_id']))->where("project_type","=",intval($type))->find()->as_array();
				#判断是不是有数据
				if($project_model['project_certs_id'] >0 && $project_model['project_certs_id']){
					#删除
					$model = ORM::factory("Projectcerts",intval($project_model['project_certs_id']));
					$model->delete();
				}
				#执行添加
				if($data['img']){ 
					$image_name = "org_".basename($data['img']);
    				$names = explode(basename($data['img']),$data['img']);
					$project = ORM::factory('Projectcerts');
	    			$project->project_img = common::getImgUrl($names[0].$image_name);
			    	$project->project_type = $type;
			    	$project->project_id = $data['project_id'];
			    	$project->project_addtime = time();
			    	$project->create();
			    	$project->clear();
			    	$bool = true;
				}
			}
			return $bool;
		}
			
			/**
			 * 修改项目推广语
			 * @author 嵇烨
			 */
			protected  function _do_update_project_advert($arr_data){
				$bool = false;
				if($arr_data){
					$model = ORM::factory("Project",intval($arr_data['project_id']));
					$model->project_advert =  $arr_data['project_advert'] ? $arr_data['project_advert'] : "";
					$model->project_temp_status = intval(1);
					$model->update();
					$model->clear();
					$bool = true;
				}
				return $bool;
			}
}
?>