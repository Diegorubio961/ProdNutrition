import { Injectable } from '@angular/core';
import { AuthInterface, RoleType } from './auth-interface';
import { Router, NavigationEnd } from '@angular/router';
import { filter } from 'rxjs/operators';

@Injectable({
  providedIn: 'root',
})
export class Auth {
  private auth: AuthInterface | null = null;

  constructor(
    private router: Router
  ) {
    // this.auth = {
    //   user: {
    //     id: 1,
    //     name: 'John Doe',
    //     email: '',
    //     identity_card: '',
    //     role: ['admin', 'nutritionist'],
    //     photoURL: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?fm=jpg&q=60&w=3000&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8dXNlciUyMHByb2ZpbGV8ZW58MHx8MHx8fDA%3D'
    //   },
    //   activeRole: 'admin',
    //   // plan: {
    //   //   id: 1,
    //   //   name: 'Basic Plan',
    //   //   description: 'This is a basic plan.'
    //   // }
    // };

    this.router.events.pipe(
      filter(event => event instanceof NavigationEnd)
    ).subscribe(() => {
      this.syncRoleFromUrl();
    });
  }


  public syncRoleFromUrl(): void {
    if (!this.auth?.user?.role) return;

    const currentUrl = this.router.url;
    
    const foundRole = this.auth.user.role.find(role => 
      currentUrl.includes(`/app/${role}`)
    );
    
    if (foundRole && this.auth.activeRole !== foundRole) {
      this.auth.activeRole = foundRole as any;
    }
  }

  public isLoggedIn(): boolean {
    return this.auth !== null;
  }

  public hasPlan(): boolean {
    if (this.auth && this.auth.user?.role.includes('client') || this.auth?.user?.role.includes('nutritionist') && this.auth.plan || this.auth?.user?.role.includes('admin')) {
      return this.auth.plan !== null;
    }
    return false;
  }

  public getRoles(native: boolean = false): string[] {
    if (this.auth?.activeRole && !native) {
      return [this.auth.activeRole];
    } else {
      return this.auth?.user?.role || [];
    }
    
  }

  public getUser(): AuthInterface | null {
    return this.auth;
  }

  public getInitials(): string {
    if (!this.auth || !this.auth.user) {
      return '';
    }
    const names = this.auth.user.name.split(' ');
    let initials = names[0].charAt(0).toUpperCase();
    if (names.length > 1) {
      initials += names[names.length - 1].charAt(0).toUpperCase();
    }
    return initials;
  }

  public switchRole(role: RoleType): void {
    if (this.auth && this.auth.user?.role.includes(role)) {
      this.auth.activeRole = role as any;
    }
  }

  public login(): void { }
}
