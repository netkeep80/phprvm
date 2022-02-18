<?
////////////////////////////////////////////////////////////////////////////////////////////////////
// ToDo: удалить class Rmodel 
class Rmodel 
{
    private static $_this = null;
    private static $_DdlValues = null;
    public $CP = null;
    
    private static function assert_this()
    {
        if( is_null(Rmodel::$_this) ) Rmodel::$_this = new Rmodel();
    }

    public static function EntDdlNvp()
    {
        global $DB;
        
        if ( !is_null(self::$_DdlValues) )
        {
            return self::$_DdlValues;
        }
        self::$_DdlValues = array();
        
        $dt = $DB->result("select EntId, EngView from rmodel");
        foreach ($dt as $row)
        {
            self::$_DdlValues[$row['EntId']] = '['.$row['EntId'].'] ' . $row['EngView'];
        }
        
        return self::$_DdlValues;
    }
    
    public static function DbModel()
    {
        Rmodel::assert_this();
        global $DB;
        return $DB->result("select    e.EntId as Id, e.EngView,
                                    s.EngView as SubEngView, r.EngView as RelEngView, o.EngView as ObjEngView,
                                    s.RusView as SubRusView, r.RusView as RelRusView, o.RusView as ObjRusView,
                                    e.PHPView,
                                    e.RusView
                            from rmodel e
                                left join rmodel s on s.EntId = e.SubId
                                left join rmodel r on r.EntId = e.RelId
                                left join rmodel o on o.EntId = e.ObjId
                            order by e.EntId");
    }
        
    public static function GetEntBySubId($SubId)
    {
        Rmodel::assert_this();
        global $DB;

        if ( '' == $SubId )
        {
            return $DB->result("select    e.EntId as Id, e.EngView,
                                        s.EngView as SubEngView,
                                        r.EngView as RelEngView,
                                        o.EngView as ObjEngView,
                                        s.RusView as SubRusView,
                                        r.RusView as RelRusView,
                                        o.RusView as ObjRusView,
                                        e.PHPView,
                                        e.RusView
                                from rmodel e
                                    left join rmodel s on s.EntId = e.SubId
                                    left join rmodel r on r.EntId = e.RelId
                                    left join rmodel o on o.EntId = e.ObjId
                                where e.EntId = e.SubId
                                order by e.EntId");
        }
        else
        {
            $Id = (int)$SubId;
            return $DB->result("select    e.EntId as Id, e.EngView,
                                        s.EngView as SubEngView,
                                        r.EngView as RelEngView,
                                        o.EngView as ObjEngView,
                                        s.RusView as SubRusView,
                                        r.RusView as RelRusView,
                                        o.RusView as ObjRusView,
                                        e.PHPView,
                                        e.RusView
                                from rmodel e
                                    left join rmodel s on s.EntId = e.SubId
                                    left join rmodel r on r.EntId = e.RelId
                                    left join rmodel o on o.EntId = e.ObjId
                                where e.SubId = '".$Id."' and e.EntId <> '".$Id."'
                                order by e.EntId");
        }
    }

////////////////////////////////////////////////////////////////////////
    public $Entities = array();

    public function __construct()
    {
    }

    function __destruct()
    {
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // old func
    public function DbModelRef($id = null)
    {
        Rmodel::assert_this();
        global $DB;
        
        $where = (is_null($id)) ? "" : " where e.EntId='$id'";
        $dt = $DB->result("
            select e.EntId as Id, e.EngView as VarEntName, e.SubId, e.RelId, e.ObjId, e.PHPView as VarValName, s.EngView as VarSubName, r.EngView as VarRelName, o.EngView as VarObjName, r.PHPView
            from rmodel e left join rmodel s on s.EntId = e.SubId left join rmodel r on r.EntId = e.RelId left join rmodel o on o.EntId = e.ObjId
            $where
        ");
        
        if (!is_array($dt) || !count($dt))
            return array();
        
        foreach ($dt as $idx=>$row)
        {
//            $dt[$idx]['PHPView'] = 'tEnt:{Ent},tSub:{Sub},tRel:{Rel},tObj:{Obj},tVal:{PHPView}';
            
            $src = array('{EntId}','{SubId}','{RelId}','{ObjId}','{PHPView}');
            $dst = array($row['VarEntName'],$row['VarSubName'],$row['VarRelName'],$row['VarObjName'],$row['VarValName']);
            
            if ( !empty($dt[$idx]['PHPView']) )
                $dt[$idx]['PHPView'] = str_replace($src,$dst,$dt[$idx]['PHPView']);
            
            return $dt[$idx];
        }
        
        Sys::debug($dt,1);
        
        return null;
    }
}

////////////////////////////////////////////////////////////////////////////////
//                              ---Ближний план---
//
//  1. доделать калькулятор...................................................5%
//  2. обновить сайт.........................................................50%
//
//  3. разобрать файл Rmodel.php что бы потом его удалить
//    * избавиться от класса Rmodel...........................................50%
//    * PHPExec.php   - exec RelId in EntId contex
//    * PHPDebug.php  - exec RelId in EntId contex (debug mode)
//
//  4. создать отображение REST WebAPI Entity.php:
//    * EntityGet    - view entity in json
//    * EntityPost   - edit entity
//    * EntityPut    - create entity in json
//    * EntityDelete - removes entity
//
//  5. сделать несколько простых примеров и проработать базовый словарь для этого:
//    * переменные и их конструкторы.........................................50%
//    * структуры
//    * ссылки...............................................................50%
//    * операции
//    * циклы
//    * конструкторы объектов
//
//  6. запихать весь возможный код в БД......................................33%
//  7. разобраться с хранением картинок в МО..................................0%
//  8. сделать 1-2 примера самомодифицирующихся моделей......................10%

//  * подумать над именами колонок в БД и переделать числовые идентификаторы в текстовые для распределения МО по сетевым узлам
//  * сделать генерацию EngView и RusView.....................................0%
//  * разграничить права, запуск и отображение только по паролю
//  * Flush all changes in Rmodel
//  * разграничение на проекты/хост-узлы?
//  * импорт/экспорт моделей/проектов/хост-узлов
//  * интеграция с PHP JSTP
//  * браузерная jsRVM (в качестве Йави использовать DOM)......................0%
//  * добавить в визуальные таблицы древовидное разворачивание структуры сущностей
//  * сделать добавление сущностей в древовидной структуре сущностей
//  * переделать на $EC........................................................0%
//   (Каждая сущность это одновременно шаблон структуры и шаблон программы по формированию этой структуры)
// 

////////////////////////////////////////////////////////////////////////////////
//                   ---Виды действий над сущностями---
//
//    2 функции - 2 базовых отношения:
//
//    1. Исполнение сущности:
// отвечает на вопрос: Как получить проекцию? exec() - исполняет проекцию сущности в контексте другой сущности,
// если проекции нет то создаёт её используя view()
//  это активация контроллера который должен спроецировать модель-объект в представление-субъект
//  исполнить можно только проекцию сущности в качестве контроллера в экземпляре отношения другой сущности
//  эта функция всегда вызывается для исполнения отношения Rel в экземпляре отношения $Ent
//  исполнится может только проекция отношения в текущий язык, т.е. $Ent['Rel']['PHPView']
//  Функция реализуется на целевой платформе
//
//    2. Отображение сущности:
// отвечает на вопрос: Куда спроецировать? view() - если в субъекте нет проекции то создаёт проекцию в нём,
// если проекции вообще нет то вызывает exec()
//  создать проекцию объекта в субъекте
//  делает конкатенацию к проекции субъекта
//  возвращает ссылку на созданную проекцию
//  Функция реализуется телом корневой сущности

$level = 0;
$enable_log = FALSE;

function RLog($mes)
{
    global $level; global $enable_log;
    if( $enable_log == TRUE )
    {
        $rows = split( "\n", $mes );
        for ($r = 0; $r < count($rows); $r++) {
            for ($i = 0; $i < $level; $i++) {
                echo '.   ';
            }
            echo $rows[$r]."\n";
        }
    }
}
    
function REnter($mes = null)
{
    global $level;
    if ( !is_null($mes) ) RLog($mes);
    RLog( "{" );
    return $level++;
}
    
function RLeave($mes = null)
{
    global $level;
    if ( !is_null($mes) ) RLog($mes);
    $level--;
    RLog( "}" );
}

function SelectRModel()
{
    global $DB;
    $rm = $DB->result("select * from rmodel order by EntId");
    foreach ($rm as $idx=>$row)
        $res[$row['EntId']] =  $row;
    return $res;
}


$EV = array();          // Йавь  - $Ent (сегмент данных, именная адресация - по именам атрибутов)
$EM = SelectRModel();   // Навь  - $rm (модель отношений, сущностная адресация - по уникальному идентификатору сущности)

//     PHPExec(): исполнение экземпляра отношения это создание или обновление проекции сущности
//  после обновления все кто использует данную сущность по ссылке будут использовать
//  обновлённый вариант проекции сущности
//  исполнение проекции контроллера в итоге должно изменить контекстную сущность $Ent и возможно агрегат $Ent['Sub']
/*
1. исполнение сущности это исполнение её PHPView в eval() который сначала надо получить
2. т.к. в итоге мы получаем определённую проекцию то в контекст eval надо передать предыдущее её состояние
3. после исполнения разумно закэшировать результат, что бы в след раз только обновить его

функция PHPExec() вызывается в следующих случаях:
1. извне для исполнения контроллера RelId в контексте EntId для получения результата проецирования
2. внутри самой себя для получения PHP проекции контроллера и получения проекции EntId
3. внутри закэшированнх тел сущностей базового словаря

Возможно в структурной проекции сущности разделить элементы и атрибуты:
1. элементов может быть много и одного типа, они составляют внутреннюю структуру сущности
 элементы формируются при участии данной сущности в качестве субъекта в других отношениях

2. атрибутов может быть только по 1 каждого типа, это проекции данной сущности
 атрибуты формируются при исполнении отношений в контексте данной сущности:
 $Ent[RelName] = PHPExec( RelId, EntId )

 Возможно атрибуты всего лишь поименованные ссылки на элементы!

 * проектор определяет метрику проекции, и в соответствии с этой метрикой располагает её в субъекте
 * у проектора PHPView метрика = 1

 * отношение всегда имеет уровень мета +1:
 1. если субъект и объект данные, то отношение есть программа
 2. если субъект и объект программа, то отношение есть генератор программы т.е. метапрограмма
 3. если субъект и объект метапрограмма, то отношение есть генератор метапрограммы т.е. метаметапрограмма
 и т.д.

 * адресация в метрике субъекта должна производиться контроллером
 * тип проекции которую создаёт контроллер по модели объекта зависит от контроллера,
 * адрес может определять как контроллер так и модель

 Пример контроллера проецирующего точку:
{
 $PHPView = &$Ent['Obj']['Color'];
 $Ent['Sub'][ $Ent['Obj']['x'] ][ $Ent['Obj']['y'] ] = &$PHPView;
}
 Этот код будет исполнен в eval() при вызове PHPExec( PointIdXXXX, 0 );

 В общем случае PHPView может быть сложным иерархическим объектом, надо решить как его исполнять?
 Как то сериализовывать в сплошной код, либо усложнить исполнитель - виртуальную машину.

 если сущность есть замкнутая структура, то её проекция должна иметь замыкание с именем её отношения
*/
function &PHPExec( integer $EntId, integer $RelId = null )
{
    if( is_null($RelId) ) $RelId = 0;   //  вызван PHPView (конструктор проекции сущности)
REnter( "PHPExec( EntId = $EntId, RelId = $RelId )" );
    global $DB; global $EV; global $EM;

    if( is_null($EM[$RelId]['PHPView']) || '' == $EM[$RelId]['PHPView'] )
    {
        $EMRel = &PHPExec( $RelId );
        $RelPHPView = $EMRel['PHPView'];//RLog(  '$RelPHPView = &PHPExec( '.$RelId.' )[\'PHPView\'];' );
    }
    else
    {
        $RelPHPView = &$EM[$RelId]['PHPView'];//RLog(  '$RelPHPView = &$EM['.$RelId.'][\'PHPView\'];' );
    }

    if( is_null($EM[$RelId]['EngView']) || '' == $EM[$RelId]['EngView'] )
    {
        $RelEngView = 'Ent'.(string)$RelId;
    }
    else
    {
        $RelEngView = &$EM[$RelId]['EngView'];
    }

    if ( !isset($EV[$EntId]) ) $EV[$EntId] = &$EM[$EntId];
    $Ent = &$EV[$EntId];    //RLog( '$Ent = &$EV['.$EntId.'];' );
    $Parent = &$Ent['Sub'];
    $Model = &$Ent['Obj'];

RLog( '$PHPView = &$Ent[\''.$RelEngView.'\'];' );
    //  возможно $RelEngView надо заменить на 'PHPView'
    $PHPView = &$Ent[$RelEngView];  //  переделать проецирование в $PHPView на проецирование в "/"

RLog( $RelPHPView );
    eval( $RelPHPView.';' );
    $Ent[$RelEngView] = &$PHPView;//RLog( '$Ent[\''.$RelEngView.'\'] = &$PHPView;' );

RLeave( 'return \''.$PHPView.'\';' );
    return $PHPView;
}

/*
[8:10:34] Sergio: чтобы $Ent.Obj.style.PHPView было ". $Ent['Obj']['tyle']['PHPView'] ."
[8:11:30] Sergio: $code = preg_replace_callback('/(\$Ent\.[a-z0-9\_\.]+)/i', function($matches) 
{
 $keys = explode('.',$matches[1]);
 $ent = array_shift($keys); // remove first element from keys - '$Ent'
 return '".' . "\$Ent['".implode("']['", $keys)."']" . '."';
}, $code);
*/

?>