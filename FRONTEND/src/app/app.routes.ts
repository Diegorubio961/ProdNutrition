import { Routes } from '@angular/router';
import { rootDispatcherGuard } from './5.guards/root-dispatcher-guard';
import { authGuard } from './5.guards/auth-guard';
import { planGuard } from './5.guards/plan-guard';
import { guestGuard } from './5.guards/guest-guard';
import { alreadyHasPlanGuard } from './5.guards/already-has-plan-guard';
import { appDispatcherGuard } from './5.guards/app-dispatcher-guard';
import { roleAdminGuard } from './5.guards/role-admin-guard';
import { roleNutritionistGuard } from './5.guards/role-nutritioninst-guard';
import { roleClientGuard } from './5.guards/role-client-guard';

export const routes: Routes = [
    {
        path: '',
        canActivate: [rootDispatcherGuard], // 1. El semáforo decide a dónde vas
        pathMatch: 'full',
        children: [] // <--- CORRECCIÓN IMPORTANTE: Angular necesita esto aunque redirijas
    },
    {
        path: 'login',
        children: [
            {
                path: '',
                loadComponent: () => import('./1.views/login/login').then(m => m.Login),
            },
            {
                path: 'register',
                loadComponent: () => import('./1.views/login/login').then(m => m.Login),
                data: { registerMode: true }
            }
        ],
        canActivate: [guestGuard] // 1. Protegemos Login: Solo para invitados
    },
    {
        path: 'packages',
        loadComponent: () => import('./1.views/packages/packages').then(m => m.Packages),
        // 2. Protegemos Packages: Solo usuarios logueados pueden ver esto
        canActivate: [authGuard, alreadyHasPlanGuard]
    },
    {
        path: 'app',
        canActivate: [authGuard, planGuard],
        children: [
            {
                path: '',
                canActivate: [appDispatcherGuard],
                children: []
            },
            {
                path: 'admin',
                canActivate: [roleAdminGuard],
                loadComponent: () => import('./1.views/layout/layout').then(m => m.Layout),
                children: [
                    {
                        path: '',
                        redirectTo: 'dashboard',
                        pathMatch: 'full'
                    },
                    {
                        path: 'nutritionist-list',
                        loadComponent: () => import('./1.views/layout/admin/nutritionist-list/nutritionist-list').then(m => m.NutritionistList),
                    },
                    {
                        path: 'plans',
                        loadComponent: () => import('./1.views/layout/admin/plans-edit/plans-edit').then(m => m.PlansEdit),
                    },
                    {
                        path: 'dashboard',
                        loadComponent: () => import('./1.views/layout/admin/dashboard/dashboard').then(m => m.Dashboard),
                    },
                ],

            },
            {
                path: 'nutritionist',
                canActivate: [roleNutritionistGuard],
                loadComponent: () => import('./1.views/layout/layout').then(m => m.Layout),
                children: [
                    {
                        path: '',
                        redirectTo: 'patients',
                        pathMatch: 'full'
                    },
                    {
                        path: 'patients',                        
                        children: [
                            {
                                path: '',
                                loadComponent: () => import('./1.views/layout/nutritionist/patients-list/patients-list').then(m => m.PatientsList),
                                children: []
                            },                            
                            {
                                path: 'clinical-history',
                                loadComponent: () => import('./1.views/layout/nutritionist/patients-list/medical-history/medical-history').then(m => m.MedicalHistory),
                                children: []
                            },
                        ]
                    },
                    {
                        path: 'calendar',
                        loadComponent: () => import('./1.views/layout/nutritionist/calendar/calendar').then(m => m.Calendar),
                        children: []
                    },
                    {
                        path: 'knowledge-base',
                        loadComponent: () => import('./1.views/layout/nutritionist/knowledge-base/knowledge-base').then(m => m.KnowledgeBase),
                        children: []
                    },
                ],
            },
            {
                path: 'client',
                canActivate: [roleClientGuard],
                loadComponent: () => import('./1.views/layout/layout').then(m => m.Layout),
            },
        ],
    },
    {
        path: '**',
        loadComponent: () => import('./4.shareds/not-found-page/not-found-page').then(m => m.NotFoundPage),
    }
];