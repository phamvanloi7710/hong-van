import { CanDeactivateFn } from '@angular/router';

export interface PageBuilderPendingChanges {
  canLeavePageBuilder(): boolean;
}

export const pageBuilderPendingChangesGuard: CanDeactivateFn<PageBuilderPendingChanges> = (
  component,
) => component.canLeavePageBuilder();
