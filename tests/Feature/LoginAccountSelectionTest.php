<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use Database\Seeders\MasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginAccountSelectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterSeeder::class);
    }

    public function test_login_page_has_separate_quick_access_accounts_for_lppm_lpm_and_pustik(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Ketua LPPM UKWK')
            ->assertSee('lppm@wkmail.ac.id')
            ->assertSee('Ketua LPM UKWK')
            ->assertSee('lpm@wkmail.ac.id')
            ->assertSee('Kepala PUSTIK UKWK')
            ->assertSee('pustik@wkmail.ac.id')
            ->assertSee('pustik123')
            ->assertDontSee('LPPM / LPM / PUSTIK UKWK');
    }

    public function test_pustik_quick_access_credentials_authenticate_the_pustik_account(): void
    {
        $pustik = Pengguna::where('email', 'pustik@wkmail.ac.id')->firstOrFail();

        $this->post(route('login'), [
            'email' => 'pustik@wkmail.ac.id',
            'password' => 'pustik123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($pustik);
        $this->assertSame('PUSTIK - Pusat Teknologi Informasi dan Komunikasi', $pustik->unit->nama_unit);
    }

    public function test_all_seeded_accounts_have_wkmail_addresses_and_role_passwords(): void
    {
        $credentials = [
            'admin@wkmail.ac.id' => 'admin123',
            'rektor@wkmail.ac.id' => 'rektor123',
            'wr1@wkmail.ac.id' => 'wr1123',
            'wr2@wkmail.ac.id' => 'wr2123',
            'wr3@wkmail.ac.id' => 'wr3123',
            'baa@wkmail.ac.id' => 'baa123',
            'bau@wkmail.ac.id' => 'bau123',
            'bka@wkmail.ac.id' => 'bka123',
            'lppm@wkmail.ac.id' => 'lppm123',
            'lpm@wkmail.ac.id' => 'lpm123',
            'pustik@wkmail.ac.id' => 'pustik123',
            'yayasan@wkmail.ac.id' => 'yayasan123',
            'feb@wkmail.ac.id' => 'feb123',
            'teknik@wkmail.ac.id' => 'teknik123',
            'fh@wkmail.ac.id' => 'fh123',
            'pertanian@wkmail.ac.id' => 'pertanian123',
            'hmjsi@wkmail.ac.id' => 'hmjsi123',
            'alumni@wkmail.ac.id' => 'alumni123',
            'mitra@wkmail.ac.id' => 'mitra123',
        ];

        foreach ($credentials as $email => $password) {
            $user = Pengguna::where('email', $email)->firstOrFail();
            $this->assertTrue(Hash::check($password, $user->password_hash), "Password uji gagal untuk {$email}.");
        }
    }
}
