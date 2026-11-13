<?php

namespace Database\Seeders;

use App\Models\Pengguna;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateDemoAccountCredentialsSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Demo credentials can only be updated in local or testing environments.');
        }

        $accounts = [
            ['admin@widyakarya.ac.id', 'admin@wkmail.ac.id', 'admin123'],
            ['rektor@widyakarya.ac.id', 'rektor@wkmail.ac.id', 'rektor123'],
            ['wr1ukwk@widyakarya.ac.id', 'wr1@wkmail.ac.id', 'wr1123'],
            ['wr2ukwk@widyakarya.ac.id', 'wr2@wkmail.ac.id', 'wr2123'],
            ['wr3ukwk@widyakarya.ac.id', 'wr3@wkmail.ac.id', 'wr3123'],
            ['baa@widyakarya.ac.id', 'baa@wkmail.ac.id', 'baa123'],
            ['bau@widyakarya.ac.id', 'bau@wkmail.ac.id', 'bau123'],
            ['bka@widyakarya.ac.id', 'bka@wkmail.ac.id', 'bka123'],
            ['lppm@widyakarya.ac.id', 'lppm@wkmail.ac.id', 'lppm123'],
            ['lpm@widyakarya.ac.id', 'lpm@wkmail.ac.id', 'lpm123'],
            ['pustik@widyakarya.ac.id', 'pustik@wkmail.ac.id', 'pustik123'],
            ['yayasan@widyakarya.ac.id', 'yayasan@wkmail.ac.id', 'yayasan123'],
            ['feb@widyakarya.ac.id', 'feb@wkmail.ac.id', 'feb123'],
            ['teknik@widyakarya.ac.id', 'teknik@wkmail.ac.id', 'teknik123'],
            ['fh@widyakarya.ac.id', 'fh@wkmail.ac.id', 'fh123'],
            ['pertanian@widyakarya.ac.id', 'pertanian@wkmail.ac.id', 'pertanian123'],
            ['hmjsi@widyakarya.ac.id', 'hmjsi@wkmail.ac.id', 'hmjsi123'],
            ['alumni@widyakarya.ac.id', 'alumni@wkmail.ac.id', 'alumni123'],
            ['mitra@institusi.co.id', 'mitra@wkmail.ac.id', 'mitra123'],
        ];

        DB::transaction(function () use ($accounts) {
            foreach ($accounts as [$oldEmail, $email, $password]) {
                $user = Pengguna::where('email', $oldEmail)->first()
                    ?? Pengguna::where('email', $email)->first();

                if (! $user) {
                    throw new RuntimeException("Demo account not found: {$oldEmail}");
                }

                if (Pengguna::where('email', $email)->where('id_pengguna', '!=', $user->id_pengguna)->exists()) {
                    throw new RuntimeException("The new demo email is already assigned: {$email}");
                }

                $user->email = $email;
                $user->password_hash = $password;
                $user->save();
            }
        });
    }
}
