<?php

namespace Database\Seeders;

use App\Models\County;
use App\Models\Town;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\Console\Helper\ProgressBar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Storage::deleteDirectory('public');

        // Create Roles and Permissions
        $this->seedRoles();

        // Create Counties and Towns
        $this->seedTowns();

        // Create Super Admin User
        $this->command->warn(PHP_EOL . 'Creating Admin User...');
        $name = 'Sntaks Admin';
        $nameArr = explode(" ", $name);
        $admin = $this->withProgressBar(1, fn () => User::factory(1)->create([
            'name' => $name,
            'username' => Str::lower("{$nameArr[0]}.{$nameArr[1]}"),
            'email' => 'sntaksolutionsltd@gmail.com',
            'phone' => $phone ='0727796831',
            'email_verified_at' => now(),
            'password' => Hash::make("{$nameArr[0]}@{$phone}"),
            'remember_token' => Str::random(10),
            'status' => 'active',
        ]))->first();
        $admin->assignRole('Super Admin');
        $this->command->info("✓ User {$admin->name} created and assigned to Super Admin role.");

        // Create Other Users
        $this->command->warn(PHP_EOL . 'Creating Non-Admin Users...');
        $users = $this->withProgressBar(20, fn () => User::factory(1)->create());
        $users->each(function (User $user) {
            $roles = ['Admin', 'Assistant Admin', 'Collections Manager', 'Collections Officer', 'Team Leader Sales', 'Sales Officer'];
            $role = $roles[array_rand($roles)];
            $user->assignRole($role);
            $this->command->info("✓ User {$user->name} created and assigned to '{$role}' role.");
        });
        $this->command->info("✅ {$users->count()} users created successfully.");
    }

    private function seedRoles(): void
    {
        $this->command->warn(PHP_EOL . 'Creating Roles and Permissions...');
        // 🔹 Define all permissions in the system
        $permissions = [
            // Dashboard
            'view_main_dashboard', 'view_sales_team_dashboard', 'view_collections_dashboard',

            // Leads
            'view_leads', 'view_lead', 'create_lead', 'update_lead', 'delete_lead',

            // Customers
            'view_customers', 'view_customer', 'create_customer', 'update_customer', 'delete_customer',

            // Loans
            'view_loans', 'view_loan', 'create_loan', 'update_loan', 'delete_loan', 'verify_loan', 'confirm_loan', 'approve_loan', 'disburse_loan',
            'clear_loan', 'mark_as_disbursed', 'mark_as_overdue', 'mark_as_default', 'mark_as_written_off',

            // Penalties
            'view_penalties', 'view_penalty', 'create_penalty', 'update_penalty', 'delete_penalty',

            // Statements, Letters, Reports
            'generate_loan_statement', 'download_loan_statement',
            'generate_settlement_statement', 'download_settlement_statement',
            'generate_clearance_letter', 'download_clearance_letter',
            // TODO: Add more permissions for reports

            // Loan Products
            'view_loan_products', 'view_loan_product', 'create_loan_product', 'update_loan_product', 'delete_loan_product',

            // Collections
            'view_collections', 'create_collections', 'update_collections', 'delete_collections',

            // Payments
            'view_payments', 'view_payment', 'create_payment', 'update_payment', 'delete_payment',

            // Counties & Towns
            'view_counties', 'view_county', 'create_county', 'update_county', 'delete_county',
            'view_towns', 'view_town', 'create_town', 'update_town', 'delete_town',

            // User & System Management
            'view_users', 'view_user', 'create_user', 'update_user', 'delete_user',
            'view_roles', 'view_role', 'create_role', 'update_role', 'delete_role',
            'view_permissions', 'view_permission', 'create_permission', 'update_permission', 'delete_permission',

            // Settings
            'manage_settings',
            'view_audit_logs', 'view_audit_log', 'create_audit_log', 'update_audit_log', 'delete_audit_log',
        ];


        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 🔹 Define roles and assign their permissions
        $roles = [
            'Super Admin' => $permissions,
            'Admin' => [
                // Dashboard
                'view_sales_team_dashboard', 'view_collections_dashboard',

                // Leads
                'view_leads', 'view_lead', 'create_lead', 'update_lead',

                // Customers
                'view_customers', 'view_customer', 'create_customer', 'update_customer',

                // Loans
                'view_loans', 'view_loan', 'create_loan', 'update_loan', 'verify_loan', 'confirm_loan', 'approve_loan', 'disburse_loan',
                'clear_loan', 'mark_as_disbursed', 'mark_as_overdue', 'mark_as_default', 'mark_as_written_off',

                // Penalties
                'view_penalties', 'view_penalty', 'create_penalty', 'update_penalty', 'delete_penalty',

                // Statements, Letters, Reports
                'generate_loan_statement', 'download_loan_statement',
                'generate_settlement_statement', 'download_settlement_statement',
                'generate_clearance_letter', 'download_clearance_letter',
                // TODO: Add more permissions for reports

                // Loan Products
                'view_loan_products', 'view_loan_product', 'create_loan_product', 'update_loan_product', 'delete_loan_product',

                // Collections
                'view_collections', 'create_collections', 'update_collections', 'delete_collections',

                // Payments
                'view_payments', 'view_payment', 'create_payment', 'update_payment', 'delete_payment',

                // Counties & Towns
                'view_counties', 'view_county', 'create_county', 'update_county', 'delete_county',
                'view_towns', 'view_town', 'create_town', 'update_town', 'delete_town',
            ],
            'Assistant Admin' => [],
            'Collections Manager' => [],
            'Collections Officer' => [],
            'Team Leader Sales' => [],
            'Sales Officer' => [],
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($perms);
        }

        $this->command->info('✅ Roles and permissions seeded successfully.');
    }

    private function seedTowns(): void {
        $this->command->warn(PHP_EOL . 'Creating Counties and Towns...');

        $counties = [
            'Mombasa', 'Kwale', 'Kilifi', 'Tana River', 'Lamu', 'Taita-Taveta', 'Garissa', 'Wajir', 'Mandera', 'Marsabit', 'Isiolo', 'Meru', 'Tharaka-Nithi', 'Embu', 'Kitui', 'Machakos', 'Makueni', 'Nyandarua', 'Nyeri', 'Kirinyaga', 'Murang’a', 'Kiambu', 'Turkana', 'West Pokot', 'Samburu', 'Trans Nzoia', 'Uasin Gishu', 'Elgeyo-Marakwet', 'Nandi', 'Baringo', 'Laikipia', 'Nakuru', 'Narok', 'Kajiado', 'Kericho', 'Bomet', 'Kakamega', 'Vihiga', 'Bungoma', 'Busia', 'Siaya', 'Kisumu', 'Homa Bay', 'Migori', 'Kisii', 'Nyamira', 'Nairobi'
        ];

        $towns = [
            'Mombasa' => ['Mombasa Town', 'Nyali', 'Likoni', 'Changamwe', 'Kisauni'],
            'Kwale' => ['Ukunda', 'Msambweni', 'Kinango', 'Lunga Lunga', 'Tiwi'],
            'Kilifi' => ['Malindi', 'Kilifi Town', 'Watamu', 'Mariakani', 'Kaloleni'],
            'Tana River' => ['Hola', 'Garsen', 'Bura', 'Kipini', 'Iddle'],
            'Lamu' => ['Lamu Town', 'Mpeketoni', 'Kiunga', 'Faza', 'Hindi'],
            'Taita-Taveta' => ['Voi', 'Taveta', 'Wundanyi', 'Mwatate', 'Bura'],
            'Garissa' => ['Garissa Town', 'Modika', 'Dadaab', 'Bura East', 'Balambala'],
            'Wajir' => ['Wajir Town', 'Griftu', 'Eldas', 'Habasswein', 'Bute'],
            'Mandera' => ['Mandera Town', 'Rhamu', 'Elwak', 'Lafey', 'Banisa'],
            'Marsabit' => ['Marsabit Town', 'Moyale', 'Laisamis', 'North Horr', 'Sololo'],
            'Isiolo' => ['Isiolo Town', 'Kinna', 'Merti', 'Garbatulla', 'Oldonyiro'],
            'Meru' => ['Meru Town', 'Maua', 'Timau', 'Nkubu', 'Laare'],
            'Tharaka-Nithi' => ['Chuka', 'Marimanti', 'Kathwana', 'Gatunga', 'Chiakariga'],
            'Embu' => ['Embu Town', 'Runyenjes', 'Siakago', 'Manyatta', 'Kiritiri'],
            'Kitui' => ['Kitui Town', 'Mutomo', 'Mwingi', 'Kyuso', 'Ikutha'],
            'Machakos' => ['Machakos Town', 'Kangundo', 'Matuu', 'Kathiani', 'Mwala'],
            'Makueni' => ['Wote', 'Makindu', 'Kibwezi', 'Sultan Hamud', 'Mtito Andei'],
            'Nyandarua' => ['Ol Kalou', 'Engineer', 'Njabini', 'Ol Jororok', 'Ndaragwa'],
            'Nyeri' => ['Nyeri Town', 'Othaya', 'Karatina', 'Mukurwe-ini', 'Chaka'],
            'Kirinyaga' => ['Kerugoya', 'Kutus', 'Kianyaga', 'Kangaita', 'Baricho'],
            'Murang’a' => ['Murang’a Town', 'Kangema', 'Kandara', 'Kiharu', 'Maragua'],
            'Kiambu' => ['Thika', 'Kiambu Town', 'Ruiru', 'Limuru', 'Githunguri'],
            'Turkana' => ['Lodwar', 'Kakuma', 'Lokichogio', 'Lokitaung', 'Lorugum'],
            'West Pokot' => ['Kapenguria', 'Chepareria', 'Sigor', 'Kacheliba', 'Orwa'],
            'Samburu' => ['Maralal', 'Baragoi', 'Wamba', 'Archers Post', 'Suguta Marmar'],
            'Trans Nzoia' => ['Kitale', 'Endebess', 'Kwanza', 'Maili Saba', 'Saboti'],
            'Uasin Gishu' => ['Eldoret', 'Turbo', 'Moi’s Bridge', 'Kesses', 'Ziwa'],
            'Elgeyo-Marakwet' => ['Iten', 'Kapsowar', 'Chebiemit', 'Tambach', 'Kamwosor'],
            'Nandi' => ['Kapsabet', 'Nandi Hills', 'Mosoriot', 'Kabiyet', 'Lessos'],
            'Baringo' => ['Kabarnet', 'Marigat', 'Mochongoi', 'Eldama Ravine', 'Tenges'],
            'Laikipia' => ['Nanyuki', 'Rumuruti', 'Doldol', 'Nyahururu', 'Kinamba'],
            'Nakuru' => ['Nakuru Town', 'Naivasha', 'Gilgil', 'Molo', 'Njoro'],
            'Narok' => ['Narok Town', 'Kilgoris', 'Ololulung’a', 'Suswa', 'Emurua Dikirr'],
            'Kajiado' => ['Kajiado Town', 'Ngong', 'Kitengela', 'Ongata Rongai', 'Namanga'],
            'Kericho' => ['Kericho Town', 'Litein', 'Kipkelion', 'Sosiot', 'Chepseon'],
            'Bomet' => ['Bomet Town', 'Sotik', 'Longisa', 'Kapkimolwo', 'Mulot'],
            'Kakamega' => ['Kakamega Town', 'Mumias', 'Lugari', 'Malava', 'Shinyalu'],
            'Vihiga' => ['Mbale', 'Luanda', 'Hamisi', 'Chavakali', 'Sabatia'],
            'Bungoma' => ['Bungoma Town', 'Webuye', 'Kimilili', 'Chwele', 'Sirisia'],
            'Busia' => ['Busia Town', 'Malaba', 'Nambale', 'Butula', 'Port Victoria'],
            'Siaya' => ['Siaya Town', 'Bondo', 'Ugunja', 'Usenge', 'Yala'],
            'Kisumu' => ['Kisumu City', 'Ahero', 'Maseno', 'Muhoroni', 'Katito'],
            'Homa Bay' => ['Homa Bay Town', 'Mbita', 'Kendu Bay', 'Oyugis', 'Rodi Kopany'],
            'Migori' => ['Migori Town', 'Awendo', 'Rongo', 'Isebania', 'Kehancha'],
            'Kisii' => ['Kisii Town', 'Ogembo', 'Nyamache', 'Tabaka', 'Suneka'],
            'Nyamira' => ['Nyamira Town', 'Keroka', 'Ekerenyo', 'Magwagwa', 'Nyansiongo'],
            'Nairobi' => ['Nairobi', 'Karen', 'Westlands', 'Embakasi', 'Kibra'],
        ];

        foreach ($counties as $countyName) {
            $county = County::create(['name' => $countyName]);

            if (isset($towns[$countyName])) {
                foreach ($towns[$countyName] as $town) {
                    Town::create([
                        'name' => $town,
                        'county_id' => $county->id,
                    ]);
                }
            } else {
                // If not predefined, generate 5 random towns
                Town::factory()->count(5)->create(['county_id' => $county->id]);
            }
        }

        $this->command->info('✅ Kenyan counties and towns seeded successfully!');
    }

    protected function withProgressBar(int $amount, \Closure $createCollectionOfOne): Collection {
        $progressBar = new ProgressBar($this->command->getOutput(), $amount);
        $progressBar->start();

        $items = new Collection;

        foreach (range(1, $amount) as $index) {
            $items = $items->merge($createCollectionOfOne());
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->getOutput()->writeln('');

        return $items;
    }
}
