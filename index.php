<?php
//ログ設定
ini_set('log_errors', 'on');
ini_set('error_log', 'php.log');
//セッション使用
session_start();

//敵達格納用
$enemies = array();

//性別クラス
class Gender{
  const MALE = 1;
  const FEMALE = 2;
  const UNKNOWN = 3;
}

//抽象クラス
//抽象クラスを継承したサブクラスは、抽象クラスにある抽象メソッドのオーバーライド必須
//サブクラスでコンストラクタを記述しなければならない
//直接インスタンス化できない
//多重継承はできない
abstract class Creature{
  //プロパティ
  //protected: 非公開だが継承可能（継承クラスまで）
  protected $name;
  protected $hp;
  protected $attackMin;
  protected $attackMax;

  //抽象メソッド
  abstract public function sayCry();

  //メソッド
  //ゲッター・セッター
  public function setName($str){
    $this->name = $str;
  }
  public function getName(){
    return $this->name;
  }
  public function setHp($num){
    $this->Hp = $num;
  }
  public function getHp(){
    return $this->Hp;
  }

  public function attack($targetObj){
    $attackPoint = mt_rand($this->attackMin, $this->attackMax);
    //if: 0=偽 0!=真
    if(!mt_rand(0, 9)){
      $attackPoint = (int)($attackPoint*1.5);
      History::set($this->getName().'のクリティカルヒット！');
    }
    $targetObj->setHp($targetObj->getHp()-$attackPoint);
    History::set($attackPoint.'のダメージ！');
  }
}

//味方クラス
class Ally extends Creature{
  //プロパティ
  protected $gender;
  //コンストラクタ
  public function __construct($name, $gender, $hp, $attackMin, $attackMax){
    $this->name = $name;
    $this->gender = $gender;
    $this->hp = $hp;
    $this->attackMin = $attackMin;
    $this->attackMax = $attackMax;
  }
  //メソッド
  //ゲッター・セッター
  public function setGender($gender){
    $this->gender = $gender;
  }
  public function getGender(){
    return $this->gender;
  }
  //抽象メソッドのオーバーライド
  public function sayCry(){
    History::set($this->name.'が叫ぶ');
    switch($this->gender){
      case Gender::MALE:
        History::set('ぐはぁっ！');
        break;
      case Gender::FEMALE:
        History::set('きゃっ！');
        break;
      case Gender::UNKNOWN:
        History::set('ぴーーーーー');
        break;
    }
  }
}

//敵クラス
class Enemy extends Creature{
  //プロパティ
  protected $img;
  //コンストラクタ
  public function __construct($name, $hp, $img, $attackMin, $attackMax){
    $this->name = $name;
    $this->hp = $hp;
    $this->img = $img;
    $this->attackMin = $attackMin;
    $this->attackMax = $attackMax;
  }
  //メソッド
  //ゲッタ-
  public function getImg(){
    return $this->img;
  }
  //抽象メソッドのオーバーライド
  public function sayCry(){
    History::set($this->name.'が叫ぶ');
    History::set('ぐはっ');
  }
}

//スーパー敵クラス
class SuperEnemy extends Enemy{
  //プロパティ
  protected $superAttack;
  //コンストラクタ
  public function __construct($name, $hp, $img, $attackMin, $attackMax, $superAttack){
    parent::__construct($name, $hp, $img, $attackMin, $attackMax);
    $this->superAttack = $superAttack;
  }
  //メソッド
  //ゲッター
  public function getSuperAttack(){
    return $this->superAttack;
  }
  //メソッドのオーバーライド
  public function attack($targetObj){
    //if: 0=偽 0!=真
    if(!mt_rand(0, 4)){
      History::set($this->name.'のスーパーアタック！');
      $targetObj->setHp($targetObj->getHp()-$this->superAttack);
      History::set($this->superAttack.'のダメージを受けた');
    }else{
      parent::attack($targetObj);
    }
  }
}

//履歴管理用インターフェース
interface HistoryInterface{
  public static function set($str);
  public static function clear();
}

// 履歴管理クラス
//インスタンス化して複数に増殖させる必要性がないクラスなので、staticにする
class History implements HistoryInterface{
  public static function set($str){
    //セッションhistoryが作られていなければ作成
    if(empty($_SESSION['history'])) $_SESSION['history'] = '';
    //セッションに文字列を詰める
    $_SESSION['history'] .= $str.'<br>';
  }
  public static function clear(){
    unset($_SESSION['history']);
  }
}

//インスタンス作成
$ally = new Ally('勇者見習い', Gender::UNKNOWN, 500, 40, 120);
$enemies[] = new Enemy( 'フランケン', 100, 'img/monster01.png', 20, 40 );
$enemies[] = new SuperEnemy( 'フランケンNEO', 300, 'img/monster02.png', 20, 60, mt_rand(50, 100) );
$enemies[] = new Enemy( 'ドラキュリー', 200, 'img/monster03.png', 30, 50 );
$enemies[] = new SuperEnemy( 'ドラキュラ男爵', 400, 'img/monster04.png', 50, 80, mt_rand(60, 120) );
$enemies[] = new Enemy( 'スカルフェイス', 150, 'img/monster05.png', 30, 60 );
$enemies[] = new Enemy( '毒ハンド', 100, 'img/monster06.png', 10, 30 );
$enemies[] = new Enemy( '泥ハンド', 120, 'img/monster07.png', 20, 30 );
$enemies[] = new Enemy( '血のハンド', 180, 'img/monster08.png', 30, 50 );


//メソッド
function createEnemy(){
  global $enemies;
  $enemy = $enemies[mt_rand(0,7)];
  History::set('野生の'.$enemy->getName().'が現れた！');
  $_SESSION['enemy'] = $enemy;
}
function createAlly(){
  global $ally;
  $_SESSION['ally'] = $ally;
}
function init(){
  History::clear();
  History::set('初期化します。');
  $_SESSION['knockDownCount'] = 0;
  createAlly();
  createEnemy();
}
function gameOver(){
  $_SESSION = array();
}

//POST送信されていた場合
if(!empty($_POST)){
  $attackFlg = (!empty($_POST['attack']))? true: false;
  $startFlg = (!empty($_POST['start']))? true: false;
  error_log('POSTされた！');

  if($startFlg){
    History::set('ゲームスタート！');
    init();
  }else{
    //攻撃をするを押した場合
    if($attackFlg){
      //敵に攻撃をする
      History::set($_SESSION['ally']->getName().'の攻撃！');
      $_SESSION['ally']->attack($_SESSION['enemy']);
      $_SESSION['enemy']->sayCry();

      //敵が攻撃をする
      History::set($_SESSION['enemy']->getName().'の攻撃！');
      $_SESSION['enemy']->attack($_SESSION['ally']);
      $_SESSION['ally']->sayCry();

      //自分のHPが0以下になったらゲームオーバー
      if($_SESSION['ally']->getHp() <= 0){
        gameOver();
      }else{
        //敵のHPがO以下になったら新しい敵を出現させる
        if($_SESSION['enemy']->getHp() <= 0){
          History::set($_SESSION['enemy']->getName().'を倒した！');
          createEnemy();
          $_SESSION['knockDownCount'] = $_SESSION['knockDownCount']+1;
        }
      }
    }else{
      //逃げるを押した場合
      History::set('逃げた！');
      createEnemy();
    }
  }
  $_POST = array();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>OBJECTIVE PRACTICE</title>
  <link rel="stylesheet" href="./style.css">
</head>
<body>
  <!-- TITLE -->
  <h1>ポケッ○モ○スター！</h1>
  <!-- MAIN -->
  <div id="main">
    <div class="black-bg position-left main_screen">
      <?php if(empty($_SESSION)){ ?>
        <h2>GAME START?</h2>
        <form method="post">
          <input type="text" name="start" value="▶ゲームスタート">
        </form>
      <?php }else{ ?>
        <h2><?php echo $_SESSION['enemy']->getName().'が現れた！'; ?></h2>
        <div>
          <img src="<?php echo $_SESSION['enemy']->getImg(); ?>">
        </div>
        <p>モンスターのHP：<?php echo $_SESSION['enemy']->getHp(); ?></p>
        <p>倒したモンスター数：<?php echo $_SESSION['knockDownCount']; ?></p>
        <p>勇者のHP：<?php echo $_SESSION['ally']->getHp(); ?></p>
        <form method="post">
          <input type="submin" name="attack" value="▶攻撃する">
          <input type="submin" name="escape" value="▶逃げる">
          <input type="submin" name="start" value="▶リスタート">
        </form>
      <?PHP } ?>
    </div>
    <!-- GAME LOG -->
    <div class="blue-bg position-right log_screen">
        <p><?php echo (!empty($_SESSION['history']))? $_SESSION['history']: '' ?></p>
    </div>
  </div>
  
</body>
</html>