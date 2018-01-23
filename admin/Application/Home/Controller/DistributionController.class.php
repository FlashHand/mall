<?php
namespace Home\Controller;

use Think\Controller;
use Think\Page;

class DistributionController extends Controller
{
    private $user_id;
    private $listRows;
    private $where = [];
    public function __construct()
    {
        parent::__construct();
        $this->setUserId($_SESSION['user_id']);
        $this->setListRows(7);//设置每页显示数目
        $this->setWhere();
    }

    /**
     * 分销记录
     */
    public function index()
    {
        $info = M('distribution as d') -> field('COUNT(*) as count , sum(price) as sumprice')->where($this->getWhere())->find();
        if($info['count'] >0){
            $page = new Page($info['count'],$this->getListRows());
            $data = M('distribution as d')->join(C('DB_PREFIX').'user as u on d.uid = u.id')-> field('d.id,d.lv,d.price,d.proportion,d.time,u.nick_name')->where($this->getWhere())->order('d.id desc')->limit($page->firstRow,$this->getListRows())->select();
            $this->assign('money',$info['sumprice']);
            $this->assign('data',$data);
            $this->assign('page',$page->show());
        }

        $this->display();
    }

    /**
     * 我的团队
     */
    public function Myteam()
    {
        $pid = M('user') -> where(['id' => $this->getUserId()]) ->getField('p_id');
        if(empty($pid)){
            $this->assign('p_name','暂无父级');
        }else{
            $p_nick_name = M('user')->where(['id' => $pid])->getField('nick_name');
            $this->assign('p_name',$p_nick_name);
        }


        $data = M('user') -> where(['p_id' => $this->getUserId()]) ->field('id,nick_name')->select();
        if(empty($data)){
            $data[0] = ['id' => 0,'nick_name' => '无下级'];
        }

        $this->assign('data',$data);

        $this->display();

    }

    public function getInfo()
    {

        if(!isset($_POST['id']) || empty($_POST['id'])){
            die;
        }
        $id = I('post.id','','intval');
        $data = M('user') -> where(['p_id' => $id])->field('id,nick_name')->select();
        if(empty($data)){
            die;
        }
//        $json = json_encode($data);
        $this->ajaxReturn($data);
    }


    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId( $user_id )
    {
        if(empty($user_id)){
            $this->error('请先登录',U('Public/Login'));
        }
        $this->user_id = $user_id;
    }

    /**
     * @return mixed
     */
    public function getListRows()
    {
        return $this->listRows;
    }

    /**
     * @param mixed $listRows
     */
    public function setListRows( $listRows )
    {
        $this->listRows = $listRows;
    }

    /**
     * @return mixed
     */
    public function getPageRows()
    {
        return $this->pageRows;
    }

    /**
     * @param mixed $pageRows
     */
    public function setPageRows( $pageRows )
    {
        $this->pageRows = $pageRows;
    }

    /**
     * @return mixed
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @param mixed $where
     */
    public function setWhere()
    {
        $this->where = ['d.p_id' => $this->getUserId()];
    }







}