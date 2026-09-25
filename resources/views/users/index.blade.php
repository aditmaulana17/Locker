@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')

@php
use App\Models\User;

$selectedRoles = request()->input('role', []);
if (is_scalar($selectedRoles) && trim((string) $selectedRoles) !== '') {
$selectedRoles = [$selectedRoles];
}
if (!is_array($selectedRoles)) {
$selectedRoles = [];
}
$selectedRoles = collect($selectedRoles)
->map(function ($role) {
$role = strtolower(trim((string) $role));
return $role === 'staf' ? 'staff' : $role;
})
->filter(fn ($role) => in_array($role, ['admin', 'pimpinan', 'staff'], true))
->unique()
->values()
->all();

$selectedStatuses = request()->input('is_active', []);
if (is_scalar($selectedStatuses) && trim((string) $selectedStatuses) !== '') {
$selectedStatuses = [$selectedStatuses];
}
if (!is_array($selectedStatuses)) {
$selectedStatuses = [];
}
$selectedStatuses = collect($selectedStatuses)
->map(fn ($status) => (string) $status)
->filter(fn ($status) => in_array($status, ['1', '0'], true))
->unique()
->values()
->all();

$search = trim((string) request()->input('search', ''));

$hasFilters =
$search !== '' ||
count($selectedRoles) > 0 ||
count($selectedStatuses) > 0;

$totalUsers = method_exists($users, 'total')
? $users->total()
: $users->count();

$allUsersCount = User::query()->count();

$activeUsersCount = User::query()
->where('is_active', true)
->count();

$inactiveUsersCount = User::query()
->where('is_active', false)
->count();

$roleCount = 3;

$roleLabels = [
'admin' => 'Admin',
'pimpinan' => 'Pimpinan',
'staff' => 'Staf',
];

$statusLabels = [
'1' => 'Aktif',
'0' => 'Nonaktif',
];
@endphp

<style>
.users-page{
    width:100%;
    max-width:1600px;
    margin:0 auto;
    padding:22px 24px 32px;
    color:#111827;
}

.users-header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
    margin-bottom:20px;
}

.users-header-left{
    min-width:0;
}

.users-title{
    margin:0;
    color:#111827;
    font-size:26px;
    line-height:1.25;
    font-weight:800;
    letter-spacing:-.025em;
}

.users-subtitle{
    margin:5px 0 0;
    color:#6b7280;
    font-size:13px;
    line-height:1.5;
}

.users-header-right{
    display:flex;
    align-items:center;
    gap:8px;
    flex-shrink:0;
}

.btn-add-user{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    min-height:40px;
    padding:0 15px;
    border:1px solid #2563eb;
    border-radius:10px;
    background:#2563eb;
    color:#fff;
    text-decoration:none;
    font-size:12px;
    font-weight:750;
    box-shadow:0 4px 12px rgba(37,99,235,.16);
    transition:all .2s ease;
}

.btn-add-user:hover{
    border-color:#1d4ed8;
    background:#1d4ed8;
    color:#fff;
    text-decoration:none;
    transform:translateY(-1px);
    box-shadow:0 6px 16px rgba(37,99,235,.22);
}

.users-summary{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:13px;
    margin-bottom:18px;
}

.summary-card{
    position:relative;
    overflow:hidden;
    display:flex;
    align-items:center;
    gap:13px;
    min-width:0;
    min-height:88px;
    padding:15px 16px;
    border:1px solid #e5e7eb;
    border-radius:14px;
    background:#fff;
    box-shadow:0 3px 14px rgba(15,23,42,.045);
    transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease;
}

.summary-card:hover{
    transform:translateY(-2px);
    border-color:#dbe3ef;
    box-shadow:0 7px 20px rgba(15,23,42,.07);
}

.summary-card::after{
    content:"";
    position:absolute;
    right:-22px;
    bottom:-30px;
    width:82px;
    height:82px;
    border-radius:50%;
    background:#f8fafc;
    opacity:.75;
    pointer-events:none;
}

.summary-icon{
    position:relative;
    z-index:1;
    display:flex;
    align-items:center;
    justify-content:center;
    width:45px;
    min-width:45px;
    height:45px;
    border-radius:12px;
}

.summary-icon-blue{
    background:#eff6ff;
    color:#2563eb;
}

.summary-icon-green{
    background:#ecfdf5;
    color:#059669;
}

.summary-icon-gray{
    background:#f3f4f6;
    color:#6b7280;
}

.summary-icon-purple{
    background:#f5f3ff;
    color:#7c3aed;
}

.summary-content{
    position:relative;
    z-index:1;
    min-width:0;
}

.summary-label{
    margin:0 0 3px;
    color:#6b7280;
    font-size:11px;
    line-height:1.4;
    font-weight:700;
}

.summary-value{
    margin:0;
    color:#111827;
    font-size:23px;
    line-height:1.1;
    font-weight:800;
    letter-spacing:-.02em;
}

.summary-note{
    margin-top:4px;
    overflow:hidden;
    color:#9ca3af;
    font-size:10px;
    line-height:1.35;
    text-overflow:ellipsis;
    white-space:nowrap;
}

.users-filter-card,
.users-table-card{
    border:1px solid #e5e7eb;
    border-radius:15px;
    background:#fff;
    box-shadow:0 3px 16px rgba(15,23,42,.04);
}

.users-filter-card{
    margin-bottom:18px;
}

.users-filter-header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    min-height:52px;
    padding:0 18px;
    border-bottom:1px solid #eef0f3;
}

.users-filter-title{
    display:flex;
    align-items:center;
    gap:8px;
    margin:0;
    color:#111827;
    font-size:13px;
    font-weight:800;
}

.users-filter-title svg{
    color:#6b7280;
}

.users-filter-body{
    padding:17px 18px;
}

.users-filter-form{
    display:grid;
    grid-template-columns:minmax(280px,1.7fr) minmax(170px,.8fr) minmax(170px,.8fr) auto;
    align-items:end;
    gap:10px;
}

.filter-group{
    min-width:0;
}

.filter-label{
    display:block;
    margin-bottom:6px;
    color:#374151;
    font-size:11px;
    font-weight:750;
}

.search-wrapper{
    position:relative;
}

.search-icon{
    position:absolute;
    top:50%;
    left:12px;
    z-index:2;
    display:flex;
    transform:translateY(-50%);
    color:#9ca3af;
    pointer-events:none;
}

.search-input{
    width:100%;
    height:40px;
    padding:0 38px;
    border:1px solid #d1d5db;
    border-radius:9px;
    outline:none;
    background:#fff;
    color:#111827;
    font-size:12px;
    transition:border-color .2s ease,box-shadow .2s ease;
}

.search-input::placeholder{
    color:#a1a1aa;
}

.search-input:focus{
    border-color:#93c5fd;
    box-shadow:0 0 0 3px rgba(59,130,246,.09);
}

.search-clear{
    position:absolute;
    top:50%;
    right:7px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    width:27px;
    height:27px;
    padding:0;
    transform:translateY(-50%);
    border:0;
    border-radius:7px;
    background:transparent;
    color:#9ca3af;
    cursor:pointer;
}

.search-clear:hover{
    background:#f3f4f6;
    color:#4b5563;
}

.filter-dropdown{
    position:relative;
}

.filter-dropdown-button{
    display:flex;
    align-items:center;
    justify-content:space-between;
    width:100%;
    height:40px;
    gap:8px;
    padding:0 11px;
    border:1px solid #d1d5db;
    border-radius:9px;
    outline:none;
    background:#fff;
    color:#374151;
    font-size:12px;
    cursor:pointer;
    transition:border-color .2s ease,box-shadow .2s ease,background .2s ease;
}

.filter-dropdown-button:hover{
    border-color:#9ca3af;
    background:#fafafa;
}

.filter-dropdown-button.active{
    border-color:#93c5fd;
    box-shadow:0 0 0 3px rgba(59,130,246,.08);
}

.filter-dropdown-button svg{
    flex-shrink:0;
    color:#9ca3af;
}

.filter-dropdown-label{
    min-width:0;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
}

.filter-dropdown-menu{
    position:absolute;
    top:calc(100% + 7px);
    left:0;
    z-index:100;
    display:none;
    width:100%;
    min-width:205px;
    max-height:280px;
    overflow-y:auto;
    padding:7px;
    border:1px solid #e5e7eb;
    border-radius:11px;
    background:#fff;
    box-shadow:0 16px 36px rgba(15,23,42,.14);
}

.filter-dropdown-menu.show{
    display:block;
}

.filter-option{
    display:flex;
    align-items:center;
    gap:9px;
    padding:8px 9px;
    border-radius:8px;
    cursor:pointer;
    transition:background .15s ease;
}

.filter-option:hover{
    background:#f8fafc;
}

.filter-option input[type=checkbox]{
    width:15px;
    height:15px;
    margin:0;
    accent-color:#2563eb;
    cursor:pointer;
}

.filter-option span{
    color:#374151;
    font-size:12px;
}

.filter-menu-divider{
    height:1px;
    margin:5px 2px;
    background:#f0f1f3;
}

.filter-menu-actions{
    display:flex;
    gap:6px;
    padding:3px 2px 1px;
}

.filter-menu-action{
    flex:1;
    min-height:31px;
    border:1px solid #e5e7eb;
    border-radius:7px;
    background:#fff;
    color:#4b5563;
    font-size:10px;
    font-weight:750;
    cursor:pointer;
}

.filter-menu-action:hover{
    background:#f9fafb;
}

.filter-menu-action.primary{
    border-color:#bfdbfe;
    background:#eff6ff;
    color:#2563eb;
}

.filter-menu-action.primary:hover{
    background:#dbeafe;
}

.filter-actions{
    display:flex;
    align-items:center;
    gap:6px;
}

.btn-filter-submit,
.btn-filter-reset{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    min-height:40px;
    padding:0 12px;
    border-radius:9px;
    font-size:11px;
    font-weight:750;
    text-decoration:none;
    white-space:nowrap;
    transition:all .2s ease;
}

.btn-filter-submit{
    border:1px solid #2563eb;
    background:#2563eb;
    color:#fff;
    cursor:pointer;
}

.btn-filter-submit:hover{
    border-color:#1d4ed8;
    background:#1d4ed8;
    color:#fff;
}

.btn-filter-reset{
    border:1px solid #d1d5db;
    background:#fff;
    color:#4b5563;
}

.btn-filter-reset:hover{
    background:#f9fafb;
    color:#111827;
    text-decoration:none;
}

.active-filter-info{
    display:flex;
    flex-wrap:wrap;
    align-items:center;
    gap:6px;
    margin-top:13px;
    padding-top:12px;
    border-top:1px solid #f1f3f5;
}

.filter-result-info{
    color:#6b7280;
    font-size:10px;
    font-weight:600;
}

.filter-badge{
    display:inline-flex;
    align-items:center;
    gap:4px;
    min-height:24px;
    padding:0 8px;
    border:1px solid #dbeafe;
    border-radius:999px;
    background:#eff6ff;
    color:#1d4ed8;
    font-size:10px;
    font-weight:700;
}

.users-table-card{
    overflow:hidden;
}

.users-table-header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    min-height:65px;
    padding:12px 18px;
    border-bottom:1px solid #eef0f3;
}

.users-table-heading{
    min-width:0;
}

.users-table-title{
    margin:0;
    color:#111827;
    font-size:14px;
    font-weight:800;
}

.users-table-description{
    margin:3px 0 0;
    color:#9ca3af;
    font-size:10px;
}

.users-count-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-height:27px;
    padding:0 10px;
    border:1px solid #e5e7eb;
    border-radius:999px;
    background:#f9fafb;
    color:#4b5563;
    font-size:10px;
    font-weight:750;
    white-space:nowrap;
}

.table-responsive{
    width:100%;
    overflow-x:auto;
}

.users-table{
    width:100%;
    min-width:860px;
    border-collapse:collapse;
}

.users-table thead th{
    padding:11px 18px;
    border-bottom:1px solid #e5e7eb;
    background:#f8fafc;
    color:#6b7280;
    font-size:10px;
    line-height:1.4;
    font-weight:800;
    text-align:left;
    text-transform:uppercase;
    letter-spacing:.045em;
    white-space:nowrap;
}

.users-table tbody td{
    padding:13px 18px;
    border-bottom:1px solid #f1f3f5;
    vertical-align:middle;
    color:#374151;
    font-size:12px;
}

.users-table tbody tr:last-child td{
    border-bottom:0;
}

.users-table tbody tr{
    transition:background .15s ease;
}

.users-table tbody tr:hover{
    background:#fafcff;
}

.user-cell{
    display:flex;
    align-items:center;
    gap:10px;
    min-width:230px;
}

.user-avatar{
    display:flex;
    align-items:center;
    justify-content:center;
    width:38px;
    min-width:38px;
    height:38px;
    border:1px solid #dbeafe;
    border-radius:11px;
    background:#eff6ff;
    color:#2563eb;
    font-size:12px;
    font-weight:800;
}

.user-info{
    min-width:0;
}

.user-name{
    margin:0;
    overflow:hidden;
    color:#111827;
    font-size:12px;
    font-weight:800;
    text-overflow:ellipsis;
    white-space:nowrap;
}

.user-email{
    margin:3px 0 0;
    overflow:hidden;
    color:#9ca3af;
    font-size:10px;
    text-overflow:ellipsis;
    white-space:nowrap;
}

.role-badge,
.status-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:4px;
    min-height:25px;
    padding:0 8px;
    border-radius:999px;
    font-size:10px;
    font-weight:750;
    white-space:nowrap;
}

.role-admin{
    border:1px solid #dbeafe;
    background:#eff6ff;
    color:#2563eb;
}

.role-pimpinan{
    border:1px solid #ede9fe;
    background:#f5f3ff;
    color:#7c3aed;
}

.role-staff{
    border:1px solid #d1fae5;
    background:#ecfdf5;
    color:#059669;
}

.status-active{
    border:1px solid #a7f3d0;
    background:#ecfdf5;
    color:#047857;
}

.status-inactive{
    border:1px solid #e5e7eb;
    background:#f3f4f6;
    color:#6b7280;
}

.action-group{
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:5px;
}

.action-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    width:32px;
    height:32px;
    padding:0;
    border:1px solid #e5e7eb;
    border-radius:8px;
    background:#fff;
    text-decoration:none;
    cursor:pointer;
    transition:all .18s ease;
}

.action-btn:hover{
    text-decoration:none;
    transform:translateY(-1px);
}

.action-view{
    color:#2563eb;
}

.action-view:hover{
    border-color:#bfdbfe;
    background:#eff6ff;
}

.action-edit{
    color:#7c3aed;
}

.action-edit:hover{
    border-color:#ddd6fe;
    background:#f5f3ff;
}

.action-delete{
    color:#dc2626;
}

.action-delete:hover{
    border-color:#fecaca;
    background:#fef2f2;
}

.users-empty{
    padding:55px 20px;
    text-align:center;
}

.users-empty-icon{
    display:flex;
    align-items:center;
    justify-content:center;
    width:56px;
    height:56px;
    margin:0 auto 13px;
    border:1px solid #e5e7eb;
    border-radius:15px;
    background:#f8fafc;
    color:#9ca3af;
}

.users-empty-title{
    margin:0;
    color:#111827;
    font-size:14px;
    font-weight:800;
}

.users-empty-text{
    max-width:440px;
    margin:6px auto 0;
    color:#9ca3af;
    font-size:11px;
    line-height:1.6;
}

.users-pagination{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:15px;
    padding:13px 18px;
    border-top:1px solid #eef0f3;
}

.pagination-info{
    color:#6b7280;
    font-size:10px;
}

.pagination-info strong{
    color:#374151;
}

.pagination-nav{
    display:flex;
    align-items:center;
    gap:4px;
}

.pagination-nav a,
.pagination-nav span{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:31px;
    height:31px;
    padding:0 7px;
    border:1px solid #e5e7eb;
    border-radius:7px;
    background:#fff;
    color:#4b5563;
    text-decoration:none;
    font-size:10px;
    font-weight:750;
}

.pagination-nav a:hover{
    background:#f9fafb;
    color:#111827;
    text-decoration:none;
}

.pagination-nav .active{
    border-color:#2563eb;
    background:#2563eb;
    color:#fff;
}

.pagination-nav .disabled{
    opacity:.45;
    cursor:not-allowed;
}

@media (min-width:701px) and (max-width:1200px){
    .users-page{
        padding:18px;
    }

    .users-summary{
        gap:9px;
    }

    .summary-card{
        min-height:82px;
        padding:12px;
        gap:9px;
        border-radius:12px;
    }

    .summary-icon{
        width:39px;
        min-width:39px;
        height:39px;
        border-radius:10px;
    }

    .summary-icon svg{
        width:18px;
        height:18px;
    }

    .summary-label{
        font-size:10px;
    }

    .summary-value{
        font-size:20px;
    }

    .summary-note{
        font-size:8px;
    }

    .users-filter-form{
        grid-template-columns:minmax(220px,1.5fr) minmax(145px,.8fr) minmax(145px,.8fr) auto;
    }

    .filter-actions{
        flex-wrap:wrap;
    }

    .btn-filter-submit,
    .btn-filter-reset{
        padding:0 9px;
    }
}

@media (max-width:900px){
    .users-filter-form{
        grid-template-columns:1fr 1fr;
    }

    .filter-group:first-child{
        grid-column:1 / -1;
    }

    .filter-actions{
        grid-column:1 / -1;
        justify-content:flex-end;
    }
}

@media (max-width:700px){
    .users-page{
        padding:15px;
    }

    .users-header{
        flex-direction:column;
        align-items:stretch;
        gap:13px;
        margin-bottom:17px;
    }

    .users-title{
        font-size:22px;
    }

    .users-subtitle{
        font-size:12px;
    }

    .users-header-right{
        width:100%;
    }

    .btn-add-user{
        width:100%;
    }

    .users-summary{
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:10px;
    }

    .summary-card{
        min-height:82px;
        padding:13px;
        gap:9px;
    }

    .summary-icon{
        width:39px;
        min-width:39px;
        height:39px;
    }

    .summary-icon svg{
        width:18px;
        height:18px;
    }

    .summary-value{
        font-size:20px;
    }

    .summary-label{
        font-size:10px;
    }

    .summary-note{
        font-size:8px;
    }

    .users-filter-header{
        min-height:49px;
        padding:0 14px;
    }

    .users-filter-body{
        padding:14px;
    }

    .users-filter-form{
        grid-template-columns:1fr;
    }

    .filter-group:first-child,
    .filter-actions{
        grid-column:auto;
    }

    .filter-actions{
        width:100%;
        flex-direction:row;
    }

    .btn-filter-submit,
    .btn-filter-reset{
        flex:1;
    }

    .users-table-header{
        align-items:flex-start;
        flex-direction:column;
        padding:13px 14px;
    }

    .users-pagination{
        align-items:stretch;
        flex-direction:column;
        padding:13px 14px;
    }

    .pagination-nav{
        justify-content:center;
        flex-wrap:wrap;
    }
}

@media (max-width:480px){
    .users-summary{
        grid-template-columns:1fr;
    }

    .summary-card{
        min-height:84px;
        padding:14px;
    }

    .summary-icon{
        width:42px;
        min-width:42px;
        height:42px;
    }

    .summary-value{
        font-size:22px;
    }

    .summary-label{
        font-size:11px;
    }

    .summary-note{
        font-size:9px;
    }

    .filter-actions{
        flex-direction:column;
    }

    .btn-filter-submit,
    .btn-filter-reset{
        width:100%;
        flex:none;
    }
}
</style>

<div class="users-page">


<div class="users-header">
    <div class="users-header-left">
        <h1 class="users-title">Manajemen Pengguna</h1>
        <p class="users-subtitle">Kelola akun pengguna, role, jabatan, dan status akun.</p>
    </div>

    <div class="users-header-right">
        @if(Route::has('users.create'))
            <a href="{{ route('users.create') }}" class="btn-add-user">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 5v14"></path>
                    <path d="M5 12h14"></path>
                </svg>
                Tambah Pengguna
            </a>
        @endif
    </div>
</div>

<div class="users-summary">

    <div class="summary-card">
        <div class="summary-icon summary-icon-blue">
            <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div class="summary-content">
            <p class="summary-label">Total Pengguna</p>
            <p class="summary-value">{{ number_format($allUsersCount, 0, ',', '.') }}</p>
            <div class="summary-note">Seluruh akun pengguna</div>
        </div>
    </div>

    <div class="summary-card">
        <div class="summary-icon summary-icon-green">
            <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 6 9 17l-5-5"></path>
            </svg>
        </div>
        <div class="summary-content">
            <p class="summary-label">Akun Aktif</p>
            <p class="summary-value">{{ number_format($activeUsersCount, 0, ',', '.') }}</p>
            <div class="summary-note">Akun yang dapat login</div>
        </div>
    </div>

    <div class="summary-card">
        <div class="summary-icon summary-icon-gray">
            <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M8 12h8"></path>
            </svg>
        </div>
        <div class="summary-content">
            <p class="summary-label">Akun Nonaktif</p>
            <p class="summary-value">{{ number_format($inactiveUsersCount, 0, ',', '.') }}</p>
            <div class="summary-note">Akun yang dinonaktifkan</div>
        </div>
    </div>

    <div class="summary-card">
        <div class="summary-icon summary-icon-purple">
            <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2 4 5v6c0 5.25 3.5 10 8 11 4.5-1 8-5.75 8-11V5l-8-3Z"></path>
                <path d="M9 12l2 2 4-4"></path>
            </svg>
        </div>
        <div class="summary-content">
            <p class="summary-label">Role</p>
            <p class="summary-value">{{ $roleCount }}</p>
            <div class="summary-note">Admin, Pimpinan, Staf</div>
        </div>
    </div>

</div>

<div class="users-filter-card">

    <div class="users-filter-header">
        <h2 class="users-filter-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            Filter Pengguna
        </h2>
    </div>

    <div class="users-filter-body">

        <form action="{{ url()->current() }}" method="GET" class="users-filter-form" id="usersFilterForm">

            <div class="filter-group">
                <label for="search" class="filter-label">Cari Pengguna</label>

                <div class="search-wrapper">
                    <span class="search-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    </span>

                    <input type="text" name="search" id="search" value="{{ $search }}" class="search-input" placeholder="Nama atau email..." autocomplete="off">

                    @if($search !== '')
                        <button type="button" class="search-clear" id="clearSearch" aria-label="Hapus pencarian">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>

            <div class="filter-group">
                <label class="filter-label">Role</label>

                <div class="filter-dropdown" data-dropdown>
                    <button type="button" class="filter-dropdown-button" data-dropdown-button>
                        <span class="filter-dropdown-label" data-dropdown-label>
                            @if(count($selectedRoles) === 0)
                                Semua Role
                            @elseif(count($selectedRoles) === 1)
                                {{ $roleLabels[$selectedRoles[0]] ?? 'Role' }}
                            @else
                                {{ count($selectedRoles) }} role dipilih
                            @endif
                        </span>

                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>

                    <div class="filter-dropdown-menu" data-dropdown-menu>

                        <label class="filter-option">
                            <input type="checkbox" data-role-select-all {{ count($selectedRoles) === 3 ? 'checked' : '' }}>
                            <span>Semua Role</span>
                        </label>

                        <div class="filter-menu-divider"></div>

                        <label class="filter-option">
                            <input type="checkbox" name="role[]" value="admin" data-role-option {{ in_array('admin', $selectedRoles, true) ? 'checked' : '' }}>
                            <span>Admin</span>
                        </label>

                        <label class="filter-option">
                            <input type="checkbox" name="role[]" value="pimpinan" data-role-option {{ in_array('pimpinan', $selectedRoles, true) ? 'checked' : '' }}>
                            <span>Pimpinan</span>
                        </label>

                        <label class="filter-option">
                            <input type="checkbox" name="role[]" value="staff" data-role-option {{ in_array('staff', $selectedRoles, true) ? 'checked' : '' }}>
                            <span>Staf</span>
                        </label>

                        <div class="filter-menu-divider"></div>

                        <div class="filter-menu-actions">
                            <button type="button" class="filter-menu-action primary" data-select-all-role>Pilih Semua</button>
                            <button type="button" class="filter-menu-action" data-clear-role>Bersihkan</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="filter-group">
                <label class="filter-label">Status Akun</label>

                <div class="filter-dropdown" data-dropdown>
                    <button type="button" class="filter-dropdown-button" data-dropdown-button>
                        <span class="filter-dropdown-label" data-dropdown-label>
                            @if(count($selectedStatuses) === 0)
                                Semua Status
                            @elseif(count($selectedStatuses) === 1)
                                {{ $statusLabels[$selectedStatuses[0]] ?? 'Status' }}
                            @else
                                {{ count($selectedStatuses) }} status dipilih
                            @endif
                        </span>

                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>

                    <div class="filter-dropdown-menu" data-dropdown-menu>

                        <label class="filter-option">
                            <input type="checkbox" data-status-select-all {{ count($selectedStatuses) === 2 ? 'checked' : '' }}>
                            <span>Semua Status</span>
                        </label>

                        <div class="filter-menu-divider"></div>

                        <label class="filter-option">
                            <input type="checkbox" name="is_active[]" value="1" data-status-option {{ in_array('1', $selectedStatuses, true) ? 'checked' : '' }}>
                            <span>Aktif</span>
                        </label>

                        <label class="filter-option">
                            <input type="checkbox" name="is_active[]" value="0" data-status-option {{ in_array('0', $selectedStatuses, true) ? 'checked' : '' }}>
                            <span>Nonaktif</span>
                        </label>

                        <div class="filter-menu-divider"></div>

                        <div class="filter-menu-actions">
                            <button type="button" class="filter-menu-action primary" data-select-all-status>Pilih Semua</button>
                            <button type="button" class="filter-menu-action" data-clear-status>Bersihkan</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="filter-actions">

                <button type="submit" class="btn-filter-submit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    Terapkan
                </button>

                @if($hasFilters)
                    <a href="{{ url()->current() }}" class="btn-filter-reset">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 12a9 9 0 1 0 3-6.7"></path>
                            <polyline points="3 4 3 10 9 10"></polyline>
                        </svg>
                        Reset
                    </a>
                @endif

            </div>

        </form>

        @if($hasFilters)
            <div class="active-filter-info">

                <span class="filter-result-info">Filter aktif:</span>

                @if($search !== '')
                    <span class="filter-badge">Pencarian: "{{ $search }}"</span>
                @endif

                @foreach($selectedRoles as $role)
                    <span class="filter-badge">
                        Role: {{ $roleLabels[$role] ?? ucfirst($role) }}
                    </span>
                @endforeach

                @foreach($selectedStatuses as $status)
                    <span class="filter-badge">
                        Status: {{ $statusLabels[$status] ?? $status }}
                    </span>
                @endforeach

            </div>
        @endif

    </div>
</div>

<div class="users-table-card">

    <div class="users-table-header">

        <div class="users-table-heading">
            <h2 class="users-table-title">Daftar Pengguna</h2>
            <p class="users-table-description">Menampilkan data akun pengguna sistem.</p>
        </div>

        <div class="users-count-badge">
            {{ number_format($totalUsers, 0, ',', '.') }} pengguna
        </div>

    </div>

    @if($users->count() > 0)

        <div class="table-responsive">

            <table class="users-table">

                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Role</th>
                        <th>Jabatan</th>
                        <th>Status</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($users as $user)

                        @php
                        $userRole = strtolower(trim((string) ($user->role ?? '')));
                        if ($userRole === 'staf') {
                            $userRole = 'staff';
                        }

                        $userRoleLabel = $roleLabels[$userRole] ?? ucfirst($userRole ?: 'Tidak diketahui');

                        $userName = trim((string) ($user->name ?? ''));

                        $avatarInitial = strtoupper(
                            substr(
                                $userName !== '' ? $userName : 'U',
                                0,
                                1
                            )
                        );

                        $isActive = (bool) ($user->is_active ?? false);
                        @endphp

                        <tr>

                            <td>
                                <div class="user-cell">

                                    <div class="user-avatar">
                                        {{ $avatarInitial }}
                                    </div>

                                    <div class="user-info">
                                        <p class="user-name">{{ $user->name }}</p>
                                        <p class="user-email">{{ $user->email }}</p>
                                    </div>

                                </div>
                            </td>

                            <td>
                                <span class="role-badge {{ $userRole === 'admin' ? 'role-admin' : ($userRole === 'pimpinan' ? 'role-pimpinan' : 'role-staff') }}">
                                    {{ $userRoleLabel }}
                                </span>
                            </td>

                            <td>
                                {{ $user->jabatan ?: '-' }}
                            </td>

                            <td>

                                @if($isActive)

                                    <span class="status-badge status-active">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 6 9 17l-5-5"></path>
                                        </svg>
                                        Aktif
                                    </span>

                                @else

                                    <span class="status-badge status-inactive">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="9"></circle>
                                            <path d="M8 12h8"></path>
                                        </svg>
                                        Nonaktif
                                    </span>

                                @endif

                            </td>

                            <td>

                                <div class="action-group">

                                    @if(Route::has('users.show'))
                                        <a href="{{ route('users.show', $user) }}" class="action-btn action-view" title="Detail">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12Z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </a>
                                    @endif

                                    @if(Route::has('users.edit'))
                                        <a href="{{ route('users.edit', $user) }}" class="action-btn action-edit" title="Edit">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 20h9"></path>
                                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                            </svg>
                                        </a>
                                    @endif

                                    @if(Route::has('users.destroy'))
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="delete-user-form" style="display:inline;">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="action-btn action-delete" title="Hapus">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"></path>
                                                    <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif

                                </div>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

        @if($users->hasPages())

            <div class="users-pagination">

                <div class="pagination-info">
                    Menampilkan
                    <strong>{{ $users->firstItem() ?? 0 }}</strong>
                    sampai
                    <strong>{{ $users->lastItem() ?? 0 }}</strong>
                    dari
                    <strong>{{ number_format($users->total(), 0, ',', '.') }}</strong>
                    pengguna
                </div>

                <div class="pagination-nav">

                    @if($users->onFirstPage())
                        <span class="disabled">‹</span>
                    @else
                        <a href="{{ $users->previousPageUrl() }}">‹</a>
                    @endif

                    @foreach(
                        $users->getUrlRange(
                            max(1, $users->currentPage() - 2),
                            min($users->lastPage(), $users->currentPage() + 2)
                        ) as $page => $url
                    )

                        @if($page == $users->currentPage())
                            <span class="active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}">{{ $page }}</a>
                        @endif

                    @endforeach

                    @if($users->hasMorePages())
                        <a href="{{ $users->nextPageUrl() }}">›</a>
                    @else
                        <span class="disabled">›</span>
                    @endif

                </div>

            </div>

        @endif

    @else

        <div class="users-empty">

            <div class="users-empty-icon">
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>

            <h3 class="users-empty-title">Tidak ada pengguna</h3>

            <p class="users-empty-text">
                @if($hasFilters)
                    Tidak ditemukan pengguna yang sesuai dengan pencarian atau filter yang dipilih.
                @else
                    Belum ada data pengguna yang tersedia.
                @endif
            </p>

            @if($hasFilters)
                <div style="margin-top:15px;">
                    <a href="{{ url()->current() }}" class="btn-filter-reset">
                        Reset Filter
                    </a>
                </div>
            @endif

        </div>

    @endif

</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const dropdowns = document.querySelectorAll('[data-dropdown]');

    dropdowns.forEach(function (dropdown) {

        const button = dropdown.querySelector('[data-dropdown-button]');
        const menu = dropdown.querySelector('[data-dropdown-menu]');

        if (!button || !menu) {
            return;
        }

        button.addEventListener('click', function (event) {

            event.stopPropagation();

            dropdowns.forEach(function (otherDropdown) {

                if (otherDropdown === dropdown) {
                    return;
                }

                const otherMenu = otherDropdown.querySelector('[data-dropdown-menu]');
                const otherButton = otherDropdown.querySelector('[data-dropdown-button]');

                if (otherMenu) {
                    otherMenu.classList.remove('show');
                }

                if (otherButton) {
                    otherButton.classList.remove('active');
                }

            });

            menu.classList.toggle('show');

            button.classList.toggle(
                'active',
                menu.classList.contains('show')
            );

        });

    });

    document.addEventListener('click', function () {

        dropdowns.forEach(function (dropdown) {

            const menu = dropdown.querySelector('[data-dropdown-menu]');
            const button = dropdown.querySelector('[data-dropdown-button]');

            if (menu) {
                menu.classList.remove('show');
            }

            if (button) {
                button.classList.remove('active');
            }

        });

    });

    const roleOptions = document.querySelectorAll('[data-role-option]');
    const roleSelectAll = document.querySelector('[data-role-select-all]');

    const roleDropdown = document
        .querySelector('[data-role-option]')
        ?.closest('[data-dropdown]');

    const roleDropdownLabel = roleDropdown?.querySelector('[data-dropdown-label]');

    function updateRoleLabel() {

        if (!roleDropdownLabel) {
            return;
        }

        const selected = Array.from(roleOptions)
            .filter(function (checkbox) {
                return checkbox.checked;
            });

        if (selected.length === 0) {

            roleDropdownLabel.textContent = 'Semua Role';

        } else if (selected.length === 1) {

            const labels = {
                admin: 'Admin',
                pimpinan: 'Pimpinan',
                staff: 'Staf'
            };

            roleDropdownLabel.textContent =
                labels[selected[0].value] ?? 'Role';

        } else {

            roleDropdownLabel.textContent =
                selected.length + ' role dipilih';

        }

        if (roleSelectAll) {

            roleSelectAll.checked =
                selected.length === roleOptions.length &&
                roleOptions.length > 0;

        }

    }

    roleOptions.forEach(function (checkbox) {

        checkbox.addEventListener(
            'change',
            updateRoleLabel
        );

    });

    if (roleSelectAll) {

        roleSelectAll.addEventListener(
            'change',
            function () {

                roleOptions.forEach(function (checkbox) {
                    checkbox.checked = roleSelectAll.checked;
                });

                updateRoleLabel();

            }
        );

    }

    const selectAllRoleButton =
        document.querySelector('[data-select-all-role]');

    if (selectAllRoleButton) {

        selectAllRoleButton.addEventListener(
            'click',
            function (event) {

                event.preventDefault();
                event.stopPropagation();

                roleOptions.forEach(function (checkbox) {
                    checkbox.checked = true;
                });

                updateRoleLabel();

            }
        );

    }

    const clearRoleButton =
        document.querySelector('[data-clear-role]');

    if (clearRoleButton) {

        clearRoleButton.addEventListener(
            'click',
            function (event) {

                event.preventDefault();
                event.stopPropagation();

                roleOptions.forEach(function (checkbox) {
                    checkbox.checked = false;
                });

                updateRoleLabel();

            }
        );

    }

    const statusOptions =
        document.querySelectorAll('[data-status-option]');

    const statusSelectAll =
        document.querySelector('[data-status-select-all]');

    const statusDropdown =
        document.querySelector('[data-status-option]')
        ?.closest('[data-dropdown]');

    const statusDropdownLabel =
        statusDropdown?.querySelector('[data-dropdown-label]');

    function updateStatusLabel() {

        if (!statusDropdownLabel) {
            return;
        }

        const selected = Array.from(statusOptions)
            .filter(function (checkbox) {
                return checkbox.checked;
            });

        if (selected.length === 0) {

            statusDropdownLabel.textContent = 'Semua Status';

        } else if (selected.length === 1) {

            statusDropdownLabel.textContent =
                selected[0].value === '1'
                    ? 'Aktif'
                    : 'Nonaktif';

        } else {

            statusDropdownLabel.textContent =
                selected.length + ' status dipilih';

        }

        if (statusSelectAll) {

            statusSelectAll.checked =
                selected.length === statusOptions.length &&
                statusOptions.length > 0;

        }

    }

    statusOptions.forEach(function (checkbox) {

        checkbox.addEventListener(
            'change',
            updateStatusLabel
        );

    });

    if (statusSelectAll) {

        statusSelectAll.addEventListener(
            'change',
            function () {

                statusOptions.forEach(function (checkbox) {
                    checkbox.checked = statusSelectAll.checked;
                });

                updateStatusLabel();

            }
        );

    }

    const selectAllStatusButton =
        document.querySelector('[data-select-all-status]');

    if (selectAllStatusButton) {

        selectAllStatusButton.addEventListener(
            'click',
            function (event) {

                event.preventDefault();
                event.stopPropagation();

                statusOptions.forEach(function (checkbox) {
                    checkbox.checked = true;
                });

                updateStatusLabel();

            }
        );

    }

    const clearStatusButton =
        document.querySelector('[data-clear-status]');

    if (clearStatusButton) {

        clearStatusButton.addEventListener(
            'click',
            function (event) {

                event.preventDefault();
                event.stopPropagation();

                statusOptions.forEach(function (checkbox) {
                    checkbox.checked = false;
                });

                updateStatusLabel();

            }
        );

    }

    const clearSearchButton =
        document.getElementById('clearSearch');

    const searchInput =
        document.getElementById('search');

    if (clearSearchButton && searchInput) {

        clearSearchButton.addEventListener(
            'click',
            function () {

                searchInput.value = '';
                searchInput.focus();

            }
        );

    }

    const deleteForms =
        document.querySelectorAll('.delete-user-form');

    deleteForms.forEach(function (form) {

        form.addEventListener(
            'submit',
            function (event) {

                const confirmed = confirm(
                    'Apakah Anda yakin ingin menghapus pengguna ini?'
                );

                if (!confirmed) {
                    event.preventDefault();
                }

            }
        );

    });

    updateRoleLabel();
    updateStatusLabel();

});
</script>

@endsection
