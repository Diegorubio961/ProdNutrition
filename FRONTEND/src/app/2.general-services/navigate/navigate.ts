import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { MenuItem } from '../../4.shareds/aside/aside-interface';

@Injectable({
  providedIn: 'root',
})
export class Navigate {

  constructor(private router: Router) {}

  navigate(item: MenuItem, menuItems: MenuItem[]): void {
    if (item.route) {
      this.resetAllActiveRecursively(menuItems);
      item.active = true;
      this.router.navigate([item.route]);
    }
  }

  private resetAllActiveRecursively(items: MenuItem[]) {
    items.forEach(menuItem => {
      menuItem.active = false;
      if (menuItem.children && menuItem.children.length > 0) {
        this.resetAllActiveRecursively(menuItem.children);
      }
    });
  }
}
