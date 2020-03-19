<?php


namespace app\mobile\controller;


use app\model\Book;
use app\model\Cate;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\facade\View;

class Booklist extends Base
{
    protected $bookService;

    protected function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->bookService = new \app\service\BookService();
    }

    public function index() {
        $boys = cache('cates:boys');
        if (!$boys) {
            $boys = Cate::where('gender','=',1)->select();
            cache('cates:boys', $boys,'null','redis');
        }
        $girls = cache('cates:girls');
        if (!$girls) {
            $girls = Cate::where('gender','=',2)->select();
            cache('cates:girls', $girls,'null','redis');
        }
        $cate_selector = -1;
        $words_selector = 9999;
        $end_selector = -1;

        $map = array();
        $cate = (int)input('cate_id');
        $gender = (int)input('gender');
        if ($gender == 0) $gender = 1;
        $arr = array();
        if ($cate == 0 || $cate == '-1') {
            if ($gender == 1) {
                foreach ($boys as $boy) {
                    array_push($arr, $boy['id']);
                }
            } else {
                foreach ($girls as $girl) {
                    array_push($arr, $girl['id']);
                }
            }
        } else {
            array_push($arr, $cate);
            $cate_selector = $cate;
        }
        $gender_selector = $gender;
        $map[] = ['cate_id', 'in', $arr];
        $words = (int)input('words');
        if ($words == 0 || $words == '-1') {

        } else {
            $map[] = ['words', '<=', $words];
            $words_selector = $words;
        }
        $end = (int)input('end');
        if ($end == 0 || $end == -1) {

        } else {
            $map[] = ['end', '=', $end];
            $end_selector = $end;
        }
        $data = $this->bookService->getPagedBooks(20, $this->end_point, 'id', $map);
        unset($data['page']['query']['page']);
        $param = '';
        foreach ($data['page']['query'] as $k => $v) {
            $param .= '&' . $k . '=' . $v;
        }
        View::assign([
            'books' => $data['books'],
            'boys' => $boys,
            'girls' => $girls,
            'cate_selector' => $cate_selector,
            'words_selector' => $words_selector,
            'end_selector' => $end_selector,
            'gender_selector' => $gender_selector,
            'page' => $data['page'],
            'param' => $param,
            'header' => '小说书库'
        ]);
        return view($this->tpl);
    }
}