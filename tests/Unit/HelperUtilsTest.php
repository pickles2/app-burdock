<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HelperUtilsTest extends TestCase
{
    /**
     * Test for \App\Helpers\utils::resolve_period_config()
     *
     * @return void
     */
    public function testResolvePeriodConfig()
    {

        $utils = new \App\Helpers\utils();
        $this->assertTrue( is_object($utils) );

        $this->assertFalse( $utils->resolve_period_config( 'forever' ) ); // 文字列 forever は、期限を設けないという意味として扱う

        $this->assertSame( 1, $utils->resolve_period_config( '1' ) );
        $this->assertSame( 60, $utils->resolve_period_config( '1MIN' ) );
        $this->assertSame( 60*60, $utils->resolve_period_config( '1H' ) );
        $this->assertSame( 24*60*60, $utils->resolve_period_config( '1d' ) );
        $this->assertSame( 30*24*60*60, $utils->resolve_period_config( '1M' ) );
        $this->assertSame( 365*24*60*60, $utils->resolve_period_config( '1Y' ) );

        $this->assertSame( 365*24*60*60 + 24*60*60 + 1, $utils->resolve_period_config( '1Y1D1' ) );


        // ゼロ指定
        $this->assertSame( 0, $utils->resolve_period_config( '0' ) );
        $this->assertSame( 0, $utils->resolve_period_config( '0MIN' ) );
        $this->assertSame( 0, $utils->resolve_period_config( '0H' ) );
        $this->assertSame( 0, $utils->resolve_period_config( '0D' ) );
        $this->assertSame( 0, $utils->resolve_period_config( '0M' ) );
        $this->assertSame( 0, $utils->resolve_period_config( '0Y' ) );


        // NGパターン
        $this->expectException(\Exception::class);
        $this->assertFalse( $utils->resolve_period_config( '' ) ); // 指定が空白文字列だからNG
        $this->assertFalse( $utils->resolve_period_config( 'Y1' ) ); // 数値を伴わない単位文字列を含むのでNG
        $this->assertFalse( $utils->resolve_period_config( '1Y1DD' ) ); // 数値を伴わない単位文字列を含むのでNG
        $this->assertFalse( $utils->resolve_period_config( '00' ) ); // ゼロで始まる数値を含むのでNG
        $this->assertFalse( $utils->resolve_period_config( '01' ) ); // ゼロで始まる数値を含むのでNG
        $this->assertFalse( $utils->resolve_period_config( '1Y01D' ) ); // ゼロで始まる数値を含むのでNG
        $this->assertFalse( $utils->resolve_period_config( '1Y1D01' ) ); // ゼロで始まる数値を含むのでNG

    }
}
