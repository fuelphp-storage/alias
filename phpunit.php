<?php

namespace FuelPHP\Alias {
	class Dummy {}
	class NsDummy {}
	class CacheDummy {}
}

namespace Some\Space {
	class OtherDummy {}
}

namespace Some\Other\Space {
	class AnotherDummy {}
}


namespace {
include './vendor/autoload.php';
}