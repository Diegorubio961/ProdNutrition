import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Auth } from '../../2.general-services/auth/auth';
import { getInitials } from '../../3.utils/tools_functions/tools_functions';
import { MenuItem } from './aside-interface';
import { trigger, state, style, transition, animate } from '@angular/animations';
import { Navigate } from '../../2.general-services/navigate/navigate';
import { menuItems } from '../../../environtments/configs';
import { Router } from '@angular/router';

@Component({
  selector: 'app-aside',
  imports: [
    CommonModule
  ],
  templateUrl: './aside.html',
  styleUrl: './aside.scss',
  animations: [
    trigger('slideInOut', [
      transition(':enter', [
        style({ height: 0, opacity: 0 }),
        animate('200ms ease-out', style({ height: '*', opacity: 1 }))
      ]),
      transition(':leave', [
        animate('200ms ease-in', style({ height: 0, opacity: 0 }))
      ])
    ])
  ]
})
export class Aside implements OnInit {
  public getInitials = getInitials;

  menuItems: MenuItem[] = [];

  showRoleSelector = false;

  constructor(
    public auth: Auth,
    public navigateService: Navigate,
    public router: Router
  ) { }

  ngOnInit(): void {
    this.init();
  }

  init(): void {
    this.menuItems = menuItems.filter(item => item.roles.some(role => this.auth.getRoles().includes(role)));
    this.checkActiveState(this.menuItems);
  }

  checkActiveState(items: MenuItem[]): boolean {
    let hasActiveState = false;

    const currentUrl = this.router.url.split('?')[0];

    for (const item of items) {

      item.active = false;

      if (item.route && currentUrl === item.route) {
        item.active = true;
        hasActiveState = true;
      }

      if (item.children && item.children.length > 0) {
        const childIsActive = this.checkActiveState(item.children);

        if (childIsActive) {
          item.expanded = true;
          hasActiveState = true;
        }
      }
    }

    return hasActiveState;
  }

  toggleItem(event: Event, item: any) {
    event.stopPropagation();
    item.expanded = !item.expanded;
  }

  handleNavigation(item: any) {
    if (item.route) {
      this.navigateService.navigate(item, this.menuItems);
    }
  }

  toggleRoleSelector(event: Event) {
    const length = this.auth.getUser()?.user?.role?.length || 0;
    if (length <= 1) return;

    event.stopPropagation();
    this.showRoleSelector = !this.showRoleSelector;
  }

  switchRole(role: string) {
    this.auth.switchRole(role as any);
    this.init();    
    this.navigateService.navigate(this.menuItems[0], this.menuItems);
  }
}
