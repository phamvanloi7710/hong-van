import { Directive, effect, inject, input, TemplateRef, ViewContainerRef } from '@angular/core';

import { AuthStore } from './auth.store';

@Directive({ selector: '[hvHasPermission]' })
export class HasPermissionDirective {
  private readonly template = inject(TemplateRef<unknown>);
  private readonly viewContainer = inject(ViewContainerRef);
  private readonly authStore = inject(AuthStore);
  private rendered = false;

  readonly permission = input.required<string>({ alias: 'hvHasPermission' });

  constructor() {
    effect(() => {
      const allowed = this.authStore.hasPermission(this.permission());

      if (allowed && !this.rendered) {
        this.viewContainer.createEmbeddedView(this.template);
        this.rendered = true;
      } else if (!allowed && this.rendered) {
        this.viewContainer.clear();
        this.rendered = false;
      }
    });
  }
}
