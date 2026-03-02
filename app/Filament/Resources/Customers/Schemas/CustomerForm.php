<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Enums\CustomerStatus;
use App\Enums\FaIcon;
use App\Enums\Gender;
use App\Enums\LeadStatus;
use App\Enums\ProductFrequency;
use App\Models\Bank;
use App\Models\BankBranch;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Tiptap\Core\Mark;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Personal Details')->schema([
                    SpatieMediaLibraryFileUpload::make('profile-photo')
                        ->label('Customer Photo')
                        ->image()
                        ->directory('customer/photos')
                        ->collection('profiles')
                        ->preserveFilenames()
                        ->imageEditor()
                        ->openable()
                        ->downloadable()
                        ->columnSpanFull(),
                    TextInput::make('name')
                        ->prefixIcon(FaIcon::USER)
                        ->prefixIconColor('primary')
                        ->required(),
                    TextInput::make('id_no')
                        ->label('National ID/Passport No')
                        ->unique(ignoreRecord: true)
                        ->prefixIcon(FaIcon::ID_CARD)
                        ->prefixIconColor('primary')
                        ->required()
                        ->validationMessages([
                            'required' => 'Phone number is required.',
                            'unique' => 'Phone number already exists.',
                        ]),
                    TextInput::make('phone')
                        ->label('Primary Phone Number')
                        ->tel()
                        ->unique(ignoreRecord: true)
                        ->telRegex('/^(?:\+254|254|0)(7\d{8}|1\d{8})$/')
                        ->prefixIcon(FaIcon::PHONE_SQUARE)
                        ->prefixIconColor('primary')
                        ->validationMessages([
                            'required' => 'Phone number is required.',
                            'regex' => 'Invalid phone number.',
                            'unique' => 'Phone number already exists.',
                        ])
                        ->required(),
                    TextInput::make('phone_alt')
                        ->label('Secondary Phone Number(s)')
                        ->prefixIcon(FaIcon::PHONE_SQUARE_ALT)
                        ->prefixIconColor('primary'),
                    Select::make('gender')
                        ->options(Gender::class)
                        ->native(false)
                        ->prefixIcon(FaIcon::VENUS_MARS)
                        ->prefixIconColor('primary')
                        ->required(),
                    DatePicker::make('dob')
                        ->label('Date Of Birth')
                        ->prefixIcon(FaIcon::CALENDAR)
                        ->prefixIconColor('primary'),
                    TextInput::make('work_email')
                        ->label('Work Email address')
                        ->email()
                        ->unique(ignoreRecord: true)
                        ->prefixIcon(Heroicon::AtSymbol)
                        ->prefixIconColor('primary')
                        ->validationMessages([
                            'email' => 'Invalid email address.',
                            'required' => 'Work Email address is required.',
                            'unique' => 'This email address already exists.'
                        ]),
                    TextInput::make('personal_email')
                        ->label('Personal Email address')
                        ->email()
                        ->unique(ignoreRecord: true)
                        ->prefixIcon(FaIcon::AT)
                        ->prefixIconColor('primary')
                        ->validationMessages([
                            'email' => 'Invalid email address.',
                            'required' => 'Personal Email address is required.',
                            'unique' => 'This email address already exists.'
                        ]),
                ])->columns(2)->columnSpanFull(),
                Section::make('Loan Product Details')->schema([
                    Select::make('product_id')
                        ->label('Loan Product')
                        ->relationship('product', 'name', fn (Builder $query) => $query->orderBy('name')->limit(10))
                        ->prefixIcon(Heroicon::OutlinedBriefcase)
                        ->prefixIconColor('primary')
                        ->native(false)
                        ->preload()
                        ->searchable()
                        ->createOptionForm([
                            Section::make()->schema([
                                TextInput::make('name')
                                    ->required(),
                                TextInput::make('description')
                                    ->required(),
                                TextInput::make('rate')
                                    ->required()
                                    ->numeric()
                                    ->default(0.25),
                                Select::make('frequency')
                                    ->options(ProductFrequency::class)
                                    ->native(false)
                                    ->searchable()
                                    ->default('payday')
                                    ->required(),
                            ])->columns()->columnSpanFull(),
                            Section::make()->schema([
                                Toggle::make('rolls_over')
                                    ->default(true)
                                    ->required(),
                                Toggle::make('is_active')
                                    ->default(true)
                                    ->required(),
                            ])->columns()->columnSpanFull()
                        ])
                        ->createOptionUsing(function (array $data) {
                            try {
                                return Product::query()->create($data)->id;
                            } catch (\Exception $e) {
                                throw new \Exception('Failed to create Product: ' . $e->getMessage());
                            }
                        })
                        ->createOptionAction(fn (Action $action) => $action->modalHeading('Create Product')->modalSubmitActionLabel('Create Product'))
                        ->required(),

                    Select::make('bank_id')
                        ->label('Bank')
                        ->prefixIcon(FaIcon::UNIVERSITY)
                        ->prefixIconColor('primary')
                        ->options(fn () => Bank::query()->pluck('name', 'id')->all())
                        ->native(false)
                        ->preload()
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(fn (callable $set) => $set('bank_branch_id', null))
                        ->dehydrated()
                        ->createOptionForm([
                            Section::make()->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('payday')
                                    ->default('23')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(31)
                                    ->required(),
                                Toggle::make('is_active')
                                    ->default(true)
                                    ->required(),
                            ])->columns(2)->columnSpanFull()
                        ])
                        ->createOptionUsing(function (array $data) {
                            try {
                                return Bank::query()->create($data)->id;
                            } catch (\Exception $e) {
                                throw new \Exception('Failed to create bank: ' . $e->getMessage());
                            }
                        })
                        ->createOptionAction(fn (Action $action) => $action->modalHeading('Create Bank')->modalSubmitActionLabel('Create Bank'))
                        ->required(),

                    // Bank Branch Select
                    Select::make('bank_branch_id')
                        ->label('Bank Branch')
                        ->options(function (callable $get, ?Customer $record) {
                            $bankId = $get('bank_id');
                            if (!$bankId && $record) {
                                $bankId = $record->bank_id;
                            }
                            return $bankId ? BankBranch::query()
                                ->where('bank_id', $bankId)
                                ->pluck('name', 'id') : [];
                        })
                        ->prefixIcon(FaIcon::HOTEL)
                        ->prefixIconColor('primary')
                        ->native(false)
                        ->required()
                        ->visible(fn (callable $get, ?Customer $record) => $get('bank_id') !== null || ($record && $record->bank_id))
                        ->searchable()
                        ->preload()
                        ->getOptionLabelUsing(fn ($value): ?string => BankBranch::find($value)?->name)
                        ->createOptionForm([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Toggle::make('is_active')
                                ->default(true)
                                ->required(),
                        ])
                        ->createOptionUsing(function (array $data, callable $get) {
                            try {
                                return BankBranch::create([
                                    'name' => $data['name'],
                                    'bank_id' => $get('bank_id'),
                                    'is_active' => $data['is_active'],
                                ])->id;
                            } catch (\Exception $e) {
                                throw new \Exception('Failed to create bank branch: ' . $e->getMessage());
                            }
                        })
                        ->createOptionAction(fn (Action $action) =>
                        $action->modalHeading('Create Bank Branch')
                            ->modalSubmitActionLabel('Create Branch')
                        ),
                ])->columns(fn (callable $get, ?Customer $record) => $get('bank_id') !== null || ($record && $record->bank_id) ? 3 : 2)->columnSpanFull(),
                Section::make('Administration')->schema([
                    Select::make('status')
                        ->label('Customer Status')
                        ->prefixIcon(FaIcon::SHIELD_ALT)
                        ->prefixIconColor('primary')
                        ->options(CustomerStatus::class)
                        ->default('active')
                        ->native(false)
                        ->searchable()
                        ->required(),
                    Select::make('user_id')
                        ->label('Added By')
                        ->prefixIcon(FaIcon::USER)
                        ->prefixIconColor('primary')
                        ->relationship('user', 'name', fn (Builder $query) => $query->orderBy('name')->limit(10))
                        ->native(false)
                        ->default(auth()->id())
                        ->hidden(fn () => !auth()->user()->isAdmin())
                        ->searchable()
                        ->preload(),
                    TextInput::make('loan_limit')
                        ->required()
                        ->prefixIcon(FaIcon::HASHTAG)
                        ->prefixIconColor('primary')
                        ->numeric()
                        ->default(0.0),
                ])->columns(3)->columnSpanFull()->visible(fn () => auth()->user()->isAdmin()),
                Section::make('Comments & Remarks')->schema([
                    MarkdownEditor::make('comments')
                        ->columnSpanFull(),
                    MarkdownEditor::make('collection_comments')
                        ->columnSpanFull(),
                ])->columnSpanFull()->collapsed(),
            ]);
    }
}
