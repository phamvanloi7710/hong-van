import { TestBed } from '@angular/core/testing';

import { DateTimeService } from './date-time.service';

describe('DateTimeService', () => {
  it('converts a UTC instant across the Vietnam day boundary', () => {
    TestBed.configureTestingModule({});
    const parts = TestBed.inject(DateTimeService).parts('2026-01-01T17:30:00Z');

    expect(parts).toEqual({ year: '2026', month: '01', day: '02', hour: '00', minute: '30' });
  });
});
