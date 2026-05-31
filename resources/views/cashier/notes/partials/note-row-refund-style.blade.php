<style>
  .refund-row-hoverable {
    cursor: pointer;
    transition: background-color .15s ease, box-shadow .15s ease, transform .05s ease;
  }

  .refund-row-hoverable:hover > td {
    background-color: rgba(148, 163, 184, 0.12) !important;
  }

  .refund-row-selected > td {
    background-color: rgba(30, 41, 59, 0.24) !important;
    box-shadow: inset 0 0 0 9999px rgba(30, 41, 59, 0.18);
  }

  .refund-row-selected td .refund-row-hint {
    color: #0f172a !important;
    font-weight: 700;
  }
</style>
