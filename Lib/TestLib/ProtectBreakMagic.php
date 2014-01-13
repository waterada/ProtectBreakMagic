<?php
/**
 * protected なメソッドにアクセスできるようにするラッパーオブジェクト。
 */
class ProtectBreakMagic {

    public $object;
    private $ref;

    /**
     * コンストラクタ。
     * @param object $object
     */
    public function __construct($object) {
        $this->object = $object;
        $this->ref = new ReflectionClass(get_class($object));
    }

    public function __call($method, $arguments) {
        if (! method_exists($this->object, $method) && method_exists($this->object, '__call')) {
            return call_user_func_array(array($this->object, $method), $arguments);
        }
        $methodObj = $this->ref->getMethod($method);
        if ($methodObj->isPrivate()) {
            throw new LogicException($method . '() is a private method, so you should not call it from outside even if testing.');
        }
        $methodObj->setAccessible(true);
        return $methodObj->invokeArgs($this->object, $arguments);
    }

    public function __get($name) {
        if (property_exists($this->object, $name)) {
            try {
                $propObj = $this->ref->getProperty($name);
            } catch (ReflectionException $e) {
                return $this->object->$name; //定義にはないが、動的に追加されたプロパティ
            }
            if ($propObj->isPrivate()) {
                throw new LogicException($name . ' is a private property, so you should not get-access it from outside even if testing.');
            }
            $propObj->setAccessible(true);
            return $propObj->getValue($this->object);
        } else {
            return $this->object->$name; //__get が呼ばれるように
        }
    }

    public function __set($name, $value) {
        if ($this->ref->hasProperty($name)) {
            //set the value after the accessibility is changed to the public if exists
            $propObj = $this->ref->getProperty($name);
            if ($propObj->isPrivate()) {
                throw new LogicException($name . ' is a private property, so you should not set-access it from outside even if testing.');
            }
            $propObj->setAccessible(true);
            $propObj->setValue($this->object, $value);
        } else {
            //set the value newly if not exists
            $this->object->$name = $value;
        }
    }
}

