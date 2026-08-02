import { ComponentFixture, TestBed } from '@angular/core/testing';

import { FoundationPage } from './foundation-page';

describe('FoundationPage', () => {
  let fixture: ComponentFixture<FoundationPage>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [FoundationPage],
    }).compileComponents();

    fixture = TestBed.createComponent(FoundationPage);
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(fixture.componentInstance).toBeTruthy();
  });

  it('should identify the admin foundation', () => {
    const compiled = fixture.nativeElement as HTMLElement;
    expect(compiled.querySelector('h1')?.textContent).toContain('HongVan Admin');
  });
});
