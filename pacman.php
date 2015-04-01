<?php
/**
 * GDD 2010 DevQuiz Pacman for PHP 5.3
 *
 * @author @koriym
 */

/**
 * デバック用関数 p
 */
function p($values = '') {
    $trace = debug_backtrace();
    $file = $trace[0]['file'];
    $line = $trace[0]['line'];
    $method = (isset($trace[1]['class'])) ? " ({$trace[1]['class']}" . '::' . "{$trace[1]['function']})" : '';
    $fileArray = file($file, FILE_USE_INCLUDE_PATH);
    $p = trim($fileArray[$line - 1]);
    unset($fileArray);
    preg_match("/p\((.+)[\s,\)]/", $p, $matches);
    $varName = isset($matches[1]) ? $matches[1] : '';
    //    $label = "$varName in {$file} on line {$line}$method";
    $label = "on line {$line}$method";
    $values = is_bool($values) ? ($values ? "true" : "false") : $values;
    echo "\n{$varName}=[". print_r($values, true) . "] $label\n";
}

/**
 * キャラクターインターフェイス
 *
 * @param string $MyChar 文字
 * @param int    $y      y座標
 * @paramint     $x      x座標
 *
 */
interface Character_Interface{
    public function __construct($myChar, $y, $x);
}

/**
 * キャラクター
 *
 */
abstract class Character implements Character_Interface
{
    /**
     * キャラ文字
     *
     * @var string
     */
    protected $_char;

    /**
     * X座標
     *
     * @var int
     */
    protected $_x;

    /**
     * Y座標
     *
     * @var int
     */
    protected $_y;

    /**
     * X移動
     *
     * @var int
     */
    protected $_dx = 0;

    /**
     * Y移動
     *
     * @var int
     */
    protected $_dy = 0;

    /**
     * 移動可能座標
     *
     * @var array
     */
    protected $_wayToGo = array();

    /**
     * 移動可能場所数
     *
     * @var int
     */
    protected $_wayToGoCount = 0;

    /**
     * 時計回り配列
     *
     * @var array
     */
    protected $_clockwiseDirection = array(array(0, 1), array(-1, 0), array(0, -1), array(1, 0));

    /**
     * コンストラクタ
     *
     * @param string $myChar
     * @param int    $x
     * @param int    $y
     */
    public function __construct($myChar, $x, $y)
    {
        $this->_myChar = $myChar;
        $this->_x = $x;
        $this->_y = $y;
    }

    /**
     * ポジション取得
     *
     *　@return array
     */
    public function getPosition(){
        return array($this->_x, $this->_y);
    }

    /**
     * データ取得
     *
     * @return array
     */
    public function get()
    {
        return array($this->_myChar, $this->_x, $this->_y, $this->_dx, $this->_dy);
    }

    /**
     * キャラクタの移動可能状態をセット
     *
     * @param array $maze              迷路
     * @param array $directionStrategy 移動方向戦略
     *
     * @return void
     */
    protected function _setPositionStatus($maze, $directionStrategy)
    {
        $cnt = 0;
        $this->_wayToGo = array();
        $wayToGo = array();
        foreach ($directionStrategy as $item) {
            list($dx, $dy) = $item;
            $x = $this->_x + $dx;
            $y = $this->_y + $dy;
            $isExist = isset($maze[$y][$x]);
            if ($isExist && $maze[$y][$x] === '.' || $maze[$y][$x] === ' ') {
                $this->_wayToGo[] = array('dy' => $dy, 'dx' => $dx);
                $cnt++;
            }
            $this->_wayToGoCount = $cnt;
        }
    }
}

/**
 * パックマン
 *
 */
class Pacman extends Character
{
    /**
     * 方向履歴
     *
     * @var string
     */
    private $_joyStick = '';

    /**
     * 移動足跡
     *
     * @var array
     */
    private $_footprintMap = array();

    /**
     * 移動足跡初期化
     *
     * @param int $width 幅
     * @param int $hight 高さ
     *
     * @return void
     */
    public function setFootprintMap($width, $hight)
    {
        for ($i = 0; $i < $hight ; $i++) {
            $this->_footprintMap[$i] = array_fill(0, $width, 0);
        }
    }

    /**
     * パックマン移動
     *
     * @param array        $maze     迷路
     * @param int          $time     タイム
     * @param Pacman_Dicon $strategy DIコンテナ
     *
     * @return void
     */
    public function move($maze, $time, Pacman_Dicon $dicon)
    {
        $this->_wayToGo = array();
        $funcMoveStrategy = $dicon->get('move');
        $this->_setPositionStatus($maze, $dicon->get('direction'));
        try {
            list($this->_dx, $this->_dy, $takeSnapShot) = $r = $funcMoveStrategy($this->_x, $this->_y, $this->_dx, $this->_dy, $maze, $this->_wayToGoCount, $this->_wayToGo, $this->_footprintMap, $this->_joyStick);
        } catch (Exception $e) {
            Pacman_Quiz::$pacmanThought[$this->_joyStick][$this->_dy][$this->_dx] = true;
            throw $e;
        }
        if ($takeSnapShot) {
            $c = $this->getJoyStickChar($this->_dx,$this->_dy);
            Pacman_Quiz::$pacmanThought[$this->_joyStick][$this->_dy][$this->_dx] = true;
        }
        $this->_x += $this->_dx;
        $this->_y += $this->_dy;
        $this->_joyStick .= self::getJoystickChar($this->_dx, $this->_dy);
        $this->_footprintMap[$this->_y][$this->_x]++;
        $result = array($this->_x, $this->_y, $this->_dx, $this->_dy, $takeSnapShot);
        return $result;
    }

    /**
     * 方向からジョイスティック名を取得
     *
     * @param int $dx
     * @param int $dy
     *
     * @return string
     */
    public static function getJoyStickChar($dx, $dy) {
        $joyStickCharacters = array('j', 'h', 'k', 'l', '.');
        $direction = array_search(array($dx, $dy), array(array(0, 1), array(-1, 0), array(0, -1), array(1, 0), array(0,0)));
        $result = $joyStickCharacters[$direction];
        return $result;
    }

    /**
     * 足跡文字列取得
     *
     * @return string
     */
    public function getJoystick()
    {
        return $this->_joyStick;
    }
}

/**
 * パックマンDIコンテナ
 *
 */
class Pacman_Dicon
{
    /**
     * サービス取得
     *
     * @param string $service サービス取得名
     *
     * @return mixed
     */
    public function get($service)
    {
        switch ($service) {
            case 'direction':
                $directionStrategy = array(array(-1, 0), array(0, -1), array(1, 0), array(0, 1));
                shuffle($directionStrategy);
                return $directionStrategy;
                break;
            case 'move':
                $function =  function ($x, $y, $dx, $dy, $maze, &$wayCnt, $wayToGo, $footprintMap, $joystick)
                {
                    switch ($wayCnt) {
                        case 0:
                            // 動けない
                            return array(0, 0, false);
                        case 1:
                            // 行き止まりなので唯一いける方向へ
                            $togo = $wayToGo[0];
                            return array($togo['dx'], $togo['dy'], false);
                        case 2:
                            // バックじゃない方
                            $isReverse0 = ($dx === ($wayToGo[0]['dx'] * -1) && $dy === ($wayToGo[0]['dy'] * -1));
                            $isReverse1 = ($dx === ($wayToGo[1]['dx'] * -1) && $dy === ($wayToGo[1]['dy'] * -1));
                            if ($isReverse0 || $isReverse1) {
                                $i = !$isReverse0 ? 0 : 1;
                                return array($wayToGo[$i]['dx'], $wayToGo[$i]['dy'], false);
                            } else {
                                break;
                            }
                        case 3:
                        case 4:
                            // 交差点で考える
                            break;
                    }
                    // 同じ行動はとらない
                    $wayToGoFiltered = array();
                    foreach ($wayToGo as $toGo) {
                        if (!isset(Pacman_Quiz::$pacmanThought[$joystick][$toGo['dy']][$toGo['dx']])) {
                            $wayToGoFiltered[] = $toGo;
                        } else {
                            $wayCnt--;
                        }
                    }
                    if (!$wayToGoFiltered) {
                        // どこも行けない
                        throw new Exception('no_way_to_go');
                    } else {
                        $wayToGo = $wayToGoFiltered;
                    }
                    foreach ($wayToGoFiltered as $toGo) {
                        if ($maze[$y + $toGo['dy']][$x + $toGo['dx']] === '.') {
                            return array($toGo['dx'], $toGo['dy'], true);
                        }
                    }
                    foreach ($wayToGoFiltered as $toGo) {
                        if ($footprintMap[$y + $toGo['dy']][$x + $toGo['dx']] <= 1) {
                            return array($toGo['dx'], $toGo['dy'], true);
                        }
                    }
                    foreach ($wayToGoFiltered as $toGo) {
                        $dx = $toGo['dx'];
                        $dy = $toGo['dy'];
                        while (true) {
                            if ($maze[$y + $dy][$x + $dx] === '.') {
                                $find = true;
                                break;
                            } elseif (!isset($maze[$y + $dy][$x + $dx]) || $maze[$y + $dy][$x + $dx] === '#') {
                                $find = false;
                                break;
                            }
                            $dx++;
                            $dy++;
                        }
                        if ($find === true) {
                            return array($toGo['dx'], $toGo['dy'], true);
                        }
                    }
                    $dx = $wayToGoFiltered[0]['dx'];
                    $dy = $wayToGoFiltered[0]['dy'];
                    return array($dx, $dy, true);
                };
        }
        return $function;
    }
}

/**
 * パックマンDIコンテナ 問題１用
 *
 */
class Pacman_Dicon_Q1 extends Pacman_Dicon
{
    /**
     * サービス取得
     *
     * @param string $service サービス取得名
     *
     * @return mixed
     */
        public function get($service)
    {
        switch ($service) {
            case 'direction':
                //$directionStrategy = array(array(-1, 0), array(0, -1), array(1, 0), array(0, 1), array(0, 0));
                $directionStrategy = array(array(-1, 0), array(0, -1), array(1, 0), array(0, 1));
                shuffle($directionStrategy);
                return $directionStrategy;
            case 'move':
                $function =  function ($x, $y, $dx, $dy, $maze, &$wayCnt, $wayToGo, $footprintMap, $joystick)
                {
                    if ($wayToGo) {
                        $dx = $wayToGo[0]['dx'];
                        $dy = $wayToGo[0]['dy'];
                        return array($dx, $dy, true);
                    } else {
                        throw new Exception('no_way_to_go');
                    }
                };
        }
        return $function;
    }
}

/**
 * モンスター
 */
class Monster extends Character
{
    /**
     * 最初？
     *
     * @var bool
     */
    private $_init = true;

    /**
     * モンスターL
     *
     * @var string
     */
    private $_j = 'L';

    /**
     * 移動
     *
     * @param array $maze
     * @param int   $pacmanX
     * @param int   $pacmanY
     *
     * @return void
     */
    public function move($maze, $pacmanX, $pacmanY)
    {
        $this->_wayToGo = array();
        if ($this->_init === true) {
            //時刻 t = 0 においては、初期位置の 下、左、上、右 の順で最初に進入可能なマスの方向に移動します。
            $this->_init = false;
            $this->_setPositionStatus($maze, $this->_clockwiseDirection);
            $this->_dy = $this->_wayToGo[0]['dy'];
            $this->_dx = $this->_wayToGo[0]['dx'];
        } else {
            //下、左、上、右 の順
            $this->_setPositionStatus($maze, $this->_clockwiseDirection);
            switch ($this->_wayToGoCount) {
                case 1:
                    // 行き止まりなので唯一いける方向へ
                    $togo = $this->_wayToGo[0];
                    $this->_dy = $togo['dy'];
                    $this->_dx = $togo['dx'];
                case 2:
                    // バックじゃない方
                    $isReverse = ($this->_dx === ($this->_wayToGo[0]['dx'] * -1) && $this->_dy === ($this->_wayToGo[0]['dy'] * -1));
                    if ($isReverse) {
                        $this->_dy = $this->_wayToGo[1]['dy'];
                        $this->_dx = $this->_wayToGo[1]['dx'];
                    } else {
                        $this->_dy = $this->_wayToGo[0]['dy'];
                        $this->_dx = $this->_wayToGo[0]['dx'];
                    }
                    break;
                case 3:
                case 4:
                    // モンスターに応じて
                    $method = '_move' . $this->_myChar;
                    list($this->_dx, $this->_dy) = $this->$method($maze, $pacmanX, $pacmanY);
                    if ($this->_dx == 0 && $this->_dy == 0){
                        p("error $this->_myChar");exit();
                    }
                    break;
                default:
            }
        }
        $this->_y += $this->_dy;
        $this->_x += $this->_dx;
        // もし以前パックマンがいたところに移動したら”王手”。パックマンは前にモンスターがいたところには移動できない。仮に壁にする。
        $makeMeWall = ($this->_x === $pacmanX && $this->_y === $pacmanY);
        $wall = $makeMeWall ? array('x' => $this->_x - $this->_dx, 'y' => $this->_y - $this->_dy) : false;
        $result = array($this->_x, $this->_y, $this->_myChar, $wall);
        return $result;
    }

    /**
     * モンスターV
     *
     * 敵から見た自機の相対位置を (dx, dy) と表すものとします。次のルールを上から順に適用し、最初に選ばれた方向に移動します。
     *
     * 1. dy ≠ 0 でかつ dy の符号方向にあるマスが進入可能であれば、その方向に移動します。
     * 2. dx ≠ 0 でかつ dx の符号方向にあるマスが進入可能であれば、その方向に移動します。
     * 3. 現在位置の 下、左、上、右 の順で最初に進入可能なマスの方向に移動する。
     *
     * @param array $maze    迷路
     * @param int   $pacmanX パックマンX座標
     * @param int   $pacmanX パックマンY座標
     *
     * @return array
     */
    private function _moveV(array $maze, $pacmanX, $pacmanY)
    {
        $dx = $pacmanX - $this->_x;
        $dy = $pacmanY - $this->_y;
        // 1
        if ($dy !== 0 ) {
            $ddy = $dy/abs($dy);
            if (isset($maze[$this->_y + $ddy][$this->_x]) && $maze[$this->_y + $ddy][$this->_x] !== '#') {
                return array(0, $ddy);
            }
        }
        // 2
        if ($dx !== 0 ){
            $ddx = $dx/abs($dx);
            if (isset($maze[$this->_y][$this->_x + $ddx]) && $maze[$this->_y][$this->_x + $ddx] !== '#') {
                return array($ddx, 0);
            }
        }
        // 3
        $result = array($this->_wayToGo[0]['dx'], $this->_wayToGo[0]['dy']);
        return $result;
    }

    /**
     * モンスターH
     *
     * 敵 V とほぼ同じです。唯一異なるのは 、進行方向を決めるルールのうち
     * 最初の二つのルールの適用順序が入れ替わるところです。
     *
     * @param array $maze    迷路
     * @param int   $pacmanX パックマンX座標
     * @param int   $pacmanX パックマンY座標
     *
     * @return array
     */
    private function _moveH(array $maze, $pacmanX, $pacmanY)
    {
        $dx = $pacmanX - $this->_x;
        $dy = $pacmanY - $this->_y;
        // 2
        if ($dx !== 0 ){
            $ddx = $dx/abs($dx);
            if (isset($maze[$this->_y][$this->_x + $ddx]) && $maze[$this->_y][$this->_x + $ddx] !== '#') {
                return array($ddx, 0);
            }
        }
        // 1
        if ($dy !== 0 ) {
            $ddy = $dy/abs($dy);
            if (isset($maze[$this->_y + $ddy][$this->_x]) && $maze[$this->_y + $ddy][$this->_x] !== '#') {
                return array(0, $ddy);
            }
        }
        // 3
        $result = array($this->_wayToGo[0]['dx'], $this->_wayToGo[0]['dy']);
        return $result;
    }

    /**
     * モンスターL
     *
     * 現在位置への進入方向から見て相対的に 左、前、右 の順
     * @param array $maze    迷路
     * @param int   $pacmanX パックマンX座標
     * @param int   $pacmanX パックマンY座標
     *
     * @return array
     */
    private function _moveL(array $maze, $pacmanX, $pacmanY)
    {
        $directionStrategy = $this->_getRelativeDirection(array(-1, 0, 1));
        $this->_setPositionStatus($maze, $directionStrategy, true);
        $result = array($this->_wayToGo[0]['dx'], $this->_wayToGo[0]['dy']);
        return $result;
    }

    /**
     * モンスターR
     *
     * 現在位置への進入方向から見て相対的に 右、前、左  の順
     *
     * @param array $maze    迷路
     * @param int   $pacmanX パックマンX座標
     * @param int   $pacmanX パックマンY座標
     *
     * @return array
     */
    private function _moveR(array $maze, $pacmanX, $pacmanY)
    {
        $directionStrategy = $this->_getRelativeDirection(array(1, 0, -1));
        $this->_setPositionStatus($maze, $directionStrategy, true);
        $result = array($this->_wayToGo[0]['dx'], $this->_wayToGo[0]['dy']);
        return $result;
    }

    /**
     * モンスターJ
     *
     * 最初は敵Lの行動、次回は敵Rの行動、さらに次回はまた敵Lの行動、と繰り返します。
     *
     * @param array $maze    迷路
     * @param int   $pacmanX パックマンX座標
     * @param int   $pacmanX パックマンY座標
     *
     * @return array
     */
    private function _moveJ(array $maze, $pacmanX, $pacmanY)
    {
        $method = "_move{$this->_j}";
        $result = $this->$method($maze, $pacmanX, $pacmanY);
        $this->_j = ($this->_j === 'L') ? 'R' : 'L';
        return $result;
    }

    /**
     * 進行方向に対しての相対方向（左右など）戦略の配列を作成
     *
     * @param interger $relativeDirection 1=右, -1=左
     *
     * @return array
     */
    private function _getRelativeDirection($relativeDirections)
    {
        $result = array();
        $currentDirection = array($this->_dx, $this->_dy);
        foreach ($relativeDirections as $relativeDirection) {
            $pos = array_search($currentDirection, $this->_clockwiseDirection);
            $directionIndex = $pos + $relativeDirection;
            if ($directionIndex === -1 ) {
                $directionIndex = 3;
            }
            if ($directionIndex === 4 ) {
                $directionIndex = 0;
            }
            array_push($result, $this->_clockwiseDirection[$directionIndex]);
        }
        return $result;
    }
}

/**
 * ゲーム
 *
 */
class Pacman_Game
{
    /**
     * スコア
     *
     * @var int
     */
    private $_score = 0;

    /**
     * クリアスコア
     *
     * @var int
     */
    private $_clearScore = 0;

    /**
     * 制限時間
     *
     * @var int
     */
    private $_timeOut = 50;

    /**
     * 時間
     *
     * @var int
     */
    private $_time = 0;

    /**
     * パックマン
     *
     * @var Pacman
     */
    private $_pacman;

    /**
     * モンスター
     *
     * @var array
     */
    private $_monsters = array();

    /**
     * 迷路
     *
     * @var array
     */
    private $_maze = array();

    /**
     * キャラ付迷路
     *
     * @var array
     */
    private $_mazeWithChar;

    /**
     * キャラなし迷路
     *
     * @var array
     */
    private $_mazeWithoutChar;

    /**
     * パックマンX座標
     *
     * @var int
     */
    private $_pacmanX;

    /**
     * パックマンY座標
     *
     * @var int
     */
    private $_pacmanY;

    /**
     * デバック?
     *
     * @var bool
     */
    private $_debug = false;

    /**
     * デバックアニメーション時間
     *
     * @var int
     */
    private $_debugTime = 0;

    /**
     * デバックアニメーション?
     *
     * @var bool
     */
    private $_debugAnimation = false;

    /**
     * パックマンDIコンテナ
     *
     * @var Pacman_Dicon
     */
    private $_pacmanDicon;

    /**
     * __clone
     */
    public function __clone()
    {
        $this->_pacman = clone $this->_pacman;
        $cloneMonsters = array();
        foreach ($this->_monsters as $monster) {
            $cloneMonsters[] = clone $monster;
        }
        $this->_monsters = $cloneMonsters;
    }

    /**
     * 迷路から必要なオブジェクトやプロパティをセット
     *
     * +Pacmanオブジェクト
     * +Monsterオブジェクト
     * +ドットの数
     * +キャラクターがいない迷路
     */
    private function _injectFromMaze($maze)
    {
        $point = 0;
        $this->_pacman = null;
        $this->_monsters = array();
        for ($y = 0; isset($maze[$y]); $y++) {
            for($x = 0 ; $x < count($maze[$y]); $x++) {
                $char = $maze[$y][$x];
                if ($char === '@') {
                    $this->_pacman = new Pacman($char, $x, $y);
                    $this->_pacman->setFootprintMap(count($maze[0]), count($maze));
                    $maze[$y][$x] = ' ';
                } elseif ($char === '.') {
                    $point++;
                } elseif (preg_match('/[A-Z]/', $char, $matches)) {
                    $this->_monsters[] = new Monster($char, $x, $y);
                    $maze[$y][$x] = ' ';
                }
            }
        }
        $this->_clearScore = $point;
        $this->_maze = $maze;
    }

    /**
     * 問題１
     *
     * @return void
     */
    public function _injectQuestionOne()
    {
        $maze = array();
        $maze[] = $this->_split('###########');
        $maze[] = $this->_split('#.V..#..H.#');
        $maze[] = $this->_split('#.##...##.#');
        $maze[] = $this->_split('#L#..#..R.#');
        $maze[] = $this->_split('#.#.###.#.#');
        $maze[] = $this->_split('#....@....#');
        $maze[] = $this->_split('###########');
        $this->_injectFromMaze($maze);
        $this->_pacmanDicon = new Pacman_Dicon_Q1();
        $this->_timeOut = 50;
    }

    /**
     * 問題2
     *
     * @return void
     */
    public function _injectQuestionTwo()
    {
        $maze = array();
        $maze[] = $this->_split('####################');
        $maze[] = $this->_split('###.....L..........#');
        $maze[] = $this->_split('###.##.##.##L##.##.#');
        $maze[] = $this->_split('###.##.##.##.##.##.#');
        $maze[] = $this->_split('#.L................#');
        $maze[] = $this->_split('#.##.##.##.##.##.###');
        $maze[] = $this->_split('#.##.##L##.##.##.###');
        $maze[] = $this->_split('#.................L#');
        $maze[] = $this->_split('#.#.#.#J####J#.#.#.#');
        $maze[] = $this->_split('#L.................#');
        $maze[] = $this->_split('###.##.##.##.##.##.#');
        $maze[] = $this->_split('###.##.##R##.##.##.#');
        $maze[] = $this->_split('#................R.#');
        $maze[] = $this->_split('#.##.##.##.##R##.###');
        $maze[] = $this->_split('#.##.##.##.##.##.###');
        $maze[] = $this->_split('#@....R..........###');
        $maze[] = $this->_split('####################');
        $this->_injectFromMaze($maze);
        $this->_pacmanDicon = new Pacman_Dicon();
        $this->_timeOut = 300;
    }

    /**
     * 問題3
     *
     * @return void
     */
    public function _injectQuestionThree()
    {
        $maze = array();
        $maze[] = $this->_split('##########################################################');
        $maze[] = $this->_split('#........................................................#');
        $maze[] = $this->_split('#.###.#########.###############.########.###.#####.#####.#');
        $maze[] = $this->_split('#.###.#########.###############.########.###.#####.#####.#');
        $maze[] = $this->_split('#.....#########....J.............J.......###.............#');
        $maze[] = $this->_split('#####.###.......#######.#######.########.###.#######.#####');
        $maze[] = $this->_split('#####.###.#####J#######.#######.########.###.##   ##.#####');
        $maze[] = $this->_split('#####.###L#####.##   ##L##   ##.##    ##.###.##   ##.#####');
        $maze[] = $this->_split('#####.###..H###.##   ##.##   ##.########.###.#######J#####');
        $maze[] = $this->_split('#####.#########.##   ##L##   ##.########.###.###V....#####');
        $maze[] = $this->_split('#####.#########.#######.#######..........###.#######.#####');
        $maze[] = $this->_split('#####.#########.#######.#######.########.###.#######.#####');
        $maze[] = $this->_split('#.....................L.........########..........R......#');
        $maze[] = $this->_split('#L####.##########.##.##########....##....#########.#####.#');
        $maze[] = $this->_split('#.####.##########.##.##########.##.##.##.#########.#####.#');
        $maze[] = $this->_split('#.................##............##..@.##...............R.#');
        $maze[] = $this->_split('##########################################################');
        $this->_injectFromMaze($maze);
        $this->_pacmanDicon = new Pacman_Dicon();
        $this->_timeOut = 700;
    }

    /**
     * 初期化
     *
     * @return void
     */
    public function init()
    {
        // init
        $this->_time = 0;
        $this->_score = 0;
        $this->_mazeWithChar = $this->_mazeWithoutChar = $this->_maze;
        $this->_pacmanX = $this->_pacmanY = 0;
    }

    /**
     * パックマン取得
     *
     * @return Pacman
     */
    public function getPacman()
    {
        return $this->_pacman;
    }

    /**
     * 1ゲームプレイ
     *
     * @return array
     */
    public function runGame()
    {
        // init
        $lastGame = clone $this;
        $isHit = $isTimeOut = $isClear = false;
        Pacman_Quiz::$gameCount++;
        // main
        while (!$isHit && !$isClear) {
            $this->_time++;
            $isTimeOut = ($this->_time >= $this->_timeOut);
            if ($isTimeOut === true) {
                break;
            }
            // モンスター
            $this->_runMonsters();
            // pacman
            list($this->_pacmanX, $this->_pacmanY, $dx, $dy, $takeSnapShot) = $this->_pacman->move($this->_mazeWithChar, $this->_time, $this->_pacmanDicon);
            if ($this->_mazeWithoutChar[$this->_pacmanY][$this->_pacmanX] === '.') {
                $this->_score++;
                $this->_mazeWithoutChar[$this->_pacmanY][$this->_pacmanX] = ' ';
            }
            // 描画
            $this->_mazeWithChar[$this->_pacmanY- $dy][$this->_pacmanX - $dx] = ' ';
            $this->_mazeWithChar[$this->_pacmanY][$this->_pacmanX] = '@';
            if ($takeSnapShot === true) {
                //  パックマンが曲がるのでスナップショット
                $joy = $lastGame->getPacman()->getJoystick();
                array_push(Pacman_Quiz::$games, $lastGame);
            }
            // 後処理
            $isClear = ($this->_score == $this->_clearScore);
            //            $restDot = $this->_clearScore - $this->_score;
            //            $isTimeOut = ($restDot > $this->_timeOut - $this->_time || $this->_time >= Pacman_Quiz::$minClearTime + $restDot);
            $isHit = $this->_hitCheck($this->_pacmanX, $this->_pacmanY);
            if ($this->_debug) {
                $this->_showCompositScreen($this->_mazeWithoutChar);
                usleep($this->_debugTime);
                if ($this->_debugAnimation) {
                    echo "\033[;H\033[2J"; // clear screen
                }
            }
            $joy = $this->_pacman->getJoystick();
            $lastGame = clone $this;
        }
        if ($isTimeOut) {
            //            $this->_checkRepeatGame();
            $this->_gameOver('TimeOut', $this->_mazeWithChar);
            return true;
        }
        if ($isHit) {
            throw new Exception('hit');
            return;
        }
        if ($isClear) {
            if ($this->_time >= Pacman_Quiz::$minClearTime) {
                return false;
            }
            Pacman_Quiz::$minClearTime = $this->_time;
            $this->_gameOver('Clear', $this->_mazeWithChar, true);
            return;
        }
        return;
    }

    /**
     * ゲームを繰り返していないかdebugチェック
     *
     * @return void
     */
    private function _checkRepeatGame()
    {
        static $joystat = array();

        $joy = $this->_pacman->getJoystick();
        $key = md5($joy);
        if (isset($joystat[$key])) {
            $joystat[$key]++;
            echo "repeated. $joy\n";
        } else {
            $joystat[$key] = 1;
        }
    }

    /**
     * モンスター移動
     *
     * @return void
     */
    private function _runMonsters()
    {
        $this->_mazeWithChar = $this->_mazeWithoutChar;
        foreach ($this->_monsters as $monster) {
            list($monsterX, $monsterY, $myChar, $wall) = $monster->move($this->_mazeWithoutChar, $this->_pacmanX, $this->_pacmanY);
            $this->_mazeWithChar[$monsterY][$monsterX] = $myChar;
            if (is_array($wall)) {
                $this->_mazeWithChar[$wall['y']][$wall['x']] = '#';
            }
        }
    }

    /**
     * ハイスコアセット
     *
     * @return void
     */
    public function setHighScore()
    {
        if ($this->_score > Pacman_Quiz::$highscore) {
            Pacman_Quiz::$highscore = $this->_score;
        }
    }

    /**
     * Game Over画面出力
     *
     * @param string $reason　      ゲームオーバーの理由
     * @param string $mazeWithChar　キャラクター付迷路
     *
     * @return void
     */
    public function _gameOver($reason, $mazeWithChar, $forceShow = false) {
        if ($forceShow || $this->_score > Pacman_Quiz::$highscore) {
            Pacman_Quiz::$highscore = $this->_score;
            echo $this->_showCompositScreen($mazeWithChar);
            $msg = ($reason === 'clear') ? "Game Clear" : "GAME OVER($reason)" ;
            echo "High Score:" . Pacman_Quiz::$highscore . ' total:'. Pacman_Quiz::$gameCount . " $msg\n\n";
        }
        return;
    }

    /**
     * ヒットチェック
     *
     * @param $pacmanX パックマンX座標
     * @param $pacmanY パックマンY座標
     *
     * @return bool
     */
    public function _hitCheck($pacmanX, $pacmanY)
    {
        $isHit = false;
        foreach ($this->_monsters as $monster) {
            list($monsterX, $monsterY) = $monster->getPosition();
            if ($pacmanX === $monsterX && $pacmanY == $monsterY) {
                $isHit = true;
            }
        }
        return $isHit;
    }

    /**
     * デバックモード
     *
     * @param int $time アニメーションタイム
     *
     * @return void
     */
    public function setDebug($time = 0) {
        $this->_debugTime =  $time * 1000;
        $this->_debugAnimation = (is_integer($time) && $time > 0) ? true : false;
        $this->_debug = true;
    }

    /**
     * 迷路配列作成
     *
     * @return array
     */
    private function _split($str)
    {
        $result = array();
        for ($i = 0 ; $i < strlen($str); $i++){
            $result[] = substr($str, $i, 1);
        }
        return $result;
    }

    /**
     * ゲーム画面描画
     *
     * @reutnr void
     */
    private function _showCompositScreen(array $maze, $debug = false)
    {
        $characters = $this->_monsters;
        array_push($characters, $this->_pacman);
        foreach ($characters as $character) {
            list($char, $x, $y, $dx, $dy) = $character->get();
            $maze[$y][$x] = $char;
        }
        echo "\n";
        foreach ($maze as $y) {
            echo implode('', $y) . "\n";
        }
        $point = ($this->_score === $this->_clearScore) ? ($this->_timeOut - $this->_time) + $this->_score : $this->_score;
        echo "Score:{$this->_score}/{$this->_clearScore} Point:{$point} Game:{$this->_time}/{$this->_timeOut} Stacked Game:" . count(Pacman_Quiz::$games) . " \n";
        echo "High Score: ". Pacman_Quiz::$highscore . ' total: '. Pacman_Quiz::$gameCount . "\n";
        echo "Play:" . $this->_pacman->getJoystick() . "\n";
    }
}

/**
 * パックマンクイズ
 *
 * @author akihito
 */
class Pacman_Quiz
{
    /**
     * パックマンの行動
     */
    public static $pacmanThought = array();

    /**
     * ゲーム
     */
    public static $games = array();

    /**
     * ハイスコア
     *
     * @var int
     */
    public static $highscore = 0;

    /**
     * クリア最小時間
     *
     * @var int
     */
    public static $minClearTime = 999;


    /**
     * ゲーム回数
     *
     * @var int
     */
    public static $gameCount = 0;

    /**
     * TimeOutの時にゲームをpopするか
     *
     * @var bool
     */
    private $_popOnTimeOut = false;


    /**
     * TimeOutの時にゲームをpopするかを設定
     *
     * @param bool $setPopOnTimeout
     */
    public function setPopOnTimeout($setPopOnTimeout)
    {
        $this->_setPopOnTimeout = $setPopOnTimeout;
    }

    /**
     * クイズ実行
     *
     * @return void
     */
    public function run($injector = '_injectQuestionThree', $debug = null)
    {
        $game = new Pacman_Game();
        if (isset($debug)) {
            $game->setDebug($debug);
        }
        $game->$injector();
        $game->init();
        $firstGame = clone $game;
        array_push(Pacman_Quiz::$games, $firstGame);
        do {
            if (!$game) {
                echo "All Game is Over.\n";
                break;
            }
            try {
                $result = $game->runGame();
                $game = array_pop(Pacman_Quiz::$games);
            } catch (Exception $e) {
                $game = array_pop(Pacman_Quiz::$games);
                $result = false;
            }
            $this->_showCounter();
            // Time Out
            if ($result === true && !$this->_popOnTimeOut) {
                self::$pacmanThought = array();
                self::$games = array(Pacman_Quiz::$games[0]);
                $game = array_pop(Pacman_Quiz::$games);
            }
        } while(true);
    }

    /**
     * クイズ実行　（問題１用）
     *
     * @return void
     */
    public function run1($injector = '_injectQuestionOne', $debug = null)
    {
        $game = new Pacman_Game();
        if (isset($debug)) {
            $game->setDebug($debug);
        }
        $game->$injector();
        $game->init();
        $firstGame = clone $game;
        array_push(self::$games, $firstGame);
        do {
            try {
                $result = $game->runGame();
            } catch (Exception $e) {
            }
            $game->setHighScore();
            self::$games = array();
            self::$pacmanThought = array();
            $game = clone $firstGame;
        } while(true);
    }

    /**
     * カウンタ表示
     *
     * @return void
     */
    private function _showCounter()
    {
        static $i = 0;

        if (++$i % 1000 === 0) {
            echo ".$i";
        }
    }
}

// クイズ実行
$quiz = new Pacman_Quiz();
//$quiz->setPopOnTimeout(true); // true:TimeOutしてもスタックからゲームを取り出すモード
//$quiz->run1('_injectQuestionOne');
//$quiz->run('_injectQuestionTwo');
// 問題３
//$quiz->run('_injectQuestionThree', true); //逐次画面描画
$quiz->run('_injectQuestionThree', 100);    //アニメーション
//$quiz->run('_injectQuestionThree');       //ハイスコアチャレンジ
