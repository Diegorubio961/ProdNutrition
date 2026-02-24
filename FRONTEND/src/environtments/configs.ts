import { MenuItem } from '../app/4.shareds/aside/aside-interface';

export const menuItems: MenuItem[] = [
  // admin
  {
    label: 'Dashboard',
    route: '/app/admin/dashboard',
    roles: ['admin'],
    icon: 'fa-solid fa-chart-pie',
  },
  {
    label: 'Nutricionistas',
    route: '/app/admin/nutritionist-list',
    roles: ['admin'],
    icon: 'fa-solid fa-user-nurse',
  },
  {
    label: 'Planes',
    route: '/app/admin/plans',
    roles: ['admin'],
    icon: 'fa-solid fa-clipboard-list',
  },
  //nutritionist
  {
    label: 'Pacientes',
    roles: ['nutritionist'],
    route: '/app/nutritionist/patients',
    icon: 'fa-solid fa-users',
    children: [
      {
        label: 'Historial Clínico',
        route: '/app/nutritionist/patients/clinical-history',
        roles: ['nutritionist']
      },
      {
        label: 'Medidas Antropométricas',
        roles: ['nutritionist'],
        route: '/app/nutritionist/patients/anthropometric-measures',
        children: [
          {
            label: 'IAKS',
            roles: ['nutritionist'],
          },
          {
            label: 'Medidas',
            roles: ['nutritionist'],
            children: [
              {
                label: 'Yuhasz',
                roles: ['nutritionist'],
              },
              {
                label: '5 Componentes',
                roles: ['nutritionist'],
              },
              {
                label: 'J & P',
                roles: ['nutritionist'],
              },
              {
                label: 'Slaughter',
                roles: ['nutritionist'],
              },
              {
                label: 'Durning',
                roles: ['nutritionist'],
              },
            ]
          },
          {
            label: 'Somotipo',
            roles: ['nutritionist'],
          },
          {
            label: 'Proporcionalidad',
            roles: ['nutritionist'],
          },
          {
            label: 'Maduración',
            roles: ['nutritionist'],
          },
          {
            label: 'ICC y Complexión',
            roles: ['nutritionist'],
          },
          {
            label: 'METs',
            roles: ['nutritionist'],
          },
          {
            label: 'Dips Energetica',
            roles: ['nutritionist'],
          },
          {
            label: 'Cunningham TMB',
            roles: ['nutritionist'],
          },
          {
            label: 'FAO',
            roles: ['nutritionist'],
          },
          {
            label: 'DRI',
            roles: ['nutritionist'],
          },
          {
            label: 'Periodización',
            roles: ['nutritionist'],
          },
          {
            label: 'Fx Desarrollada',
            roles: ['nutritionist'],
          },
          {
            label: 'Suplementación',
            roles: ['nutritionist'],
          },
          {
            label: 'Calculo Total Energia',
            roles: ['nutritionist'],
          },
        ]
      },
    ],
  },
  {
    label: 'Agenda y Calendario',
    route: '/app/nutritionist/calendar',
    roles: ['nutritionist'],
    icon: 'fa-solid fa-calendar',
  },
  {
    label: 'Base de Conocimientos',
    route: '/app/nutritionist/knowledge-base',
    roles: ['nutritionist'],
    icon: 'fa-solid fa-book',
  },
  // client
  {
    label: 'Perfil',
    route: '/app/client/profile',
    roles: ['client'],
    icon: 'fa-solid fa-user',
  },
  {
    label: 'Agenda y Calendario',
    route: '/app/client/calendar',
    roles: ['client'],
    icon: 'fa-solid fa-calendar',
  },
  {
    label: 'Dashboard',
    route: '/app/client/dashboard',
    roles: ['client'],
    icon: 'fa-solid fa-chart-pie',
  },
];