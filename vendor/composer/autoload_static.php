<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit55e6d3849ce53d5a9a8929ccb2494bf8
{
    public static $classMap = array (
        'ComposerAutoloaderInit55e6d3849ce53d5a9a8929ccb2494bf8' => __DIR__ . '/..' . '/composer/autoload_real.php',
        'Composer\\Autoload\\ClassLoader' => __DIR__ . '/..' . '/composer/ClassLoader.php',
        'Composer\\Autoload\\ComposerStaticInit55e6d3849ce53d5a9a8929ccb2494bf8' => __DIR__ . '/..' . '/composer/autoload_static.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'chess\\Bishop' => __DIR__ . '/../..' . '/Bishop.php',
        'chess\\Board' => __DIR__ . '/../..' . '/Board.php',
        'chess\\King' => __DIR__ . '/../..' . '/King.php',
        'chess\\Knight' => __DIR__ . '/../..' . '/Knight.php',
        'chess\\ML' => __DIR__ . '/../..' . '/ML.php',
        'chess\\Pawn' => __DIR__ . '/../..' . '/Pawn.php',
        'chess\\Piece' => __DIR__ . '/../..' . '/Piece.php',
        'chess\\Queen' => __DIR__ . '/../..' . '/Queen.php',
        'chess\\Rook' => __DIR__ . '/../..' . '/Rook.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit55e6d3849ce53d5a9a8929ccb2494bf8::$classMap;

        }, null, ClassLoader::class);
    }
}