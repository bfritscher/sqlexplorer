import { API_URL } from "../../config";

export default function createAssignment() {
  const name = prompt("Name of assignment?");
  if (!name) return;
  const currentYear = new Date().getFullYear();
  const year = prompt("Year", currentYear);
  if (year === undefined) return;
  var course = prompt("Course");
  if (course === undefined) return;
  return fetch(`${API_URL}/api/assignment`, {
    method: "POST",
    credentials: "include",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ name, year, course }),
  }).then((res) => res.json());
}
