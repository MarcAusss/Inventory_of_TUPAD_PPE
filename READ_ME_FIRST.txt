TSSD DISTRIBUTION + CALL-OFF NUMBER REQUEST LETTER REVISION
============================================================

HOW TO APPLY
1. Back up or commit your current Laravel project.
2. Extract this ZIP directly into the Laravel project root.
3. Allow Windows to merge folders and replace the included files.
4. From the Laravel project root, run:

   php artisan migrate
   php artisan optimize:clear
   npm run build

No composer update is required for this revision.

WHAT WAS ADDED
- The normal TSSD Distribution page remains the main workflow.
- A Call-Off Number Request Letter Settings section now appears below the
  distribution remarks and above the final Save button.
- The selected Purchase Order amount is loaded automatically into the letter.
- TSSD can open a print-style preview using current unsaved provincial PPE
  allocations. Previewing does not save or submit the distribution.
- Saving the distribution also stores and submits the generated request letter
  to the Supply Unit.
- The TSSD Call-Off Request Letters index now shows both pending requests and
  approved Call-Off records.
- Supply can view the generated TSSD request letter before assigning the
  official Call-Off Number.
- When Supply assigns and approves the Call-Off, the saved letter settings are
  copied into the official Call-Off record.
- The letter table now includes Face Mask quantities as well as every other PPE
  category from the distribution.

QUICK TEST
1. Log in as TSSD Unit.
2. Open Create TSSD Distribution.
3. Select a Purchase Order and assign PPE to the provinces.
4. Complete the request-letter settings at the bottom.
5. Click Open Call-Off Letter Preview and confirm the unsaved data appears.
6. Close the preview and click Save Distribution & Submit Letter.
7. Open TSSD > Call-Off Letters and confirm it shows Pending Call-Off Number.
8. Log in as Supply Unit, open Call-Off Approvals, select the batch, and click
   View TSSD Request Letter.
9. Assign the official Call-Off Number and approve it.
